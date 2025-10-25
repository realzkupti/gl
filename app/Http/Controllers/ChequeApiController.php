<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Branch;
use App\Models\Cheque;
use App\Models\ChequeTemplate;


class ChequeApiController extends Controller
{
    // Serve the static Cheque UI (reads HTML from resources)
    public function ui()
    {
        $path = resource_path('views/cheque/Cheque2.html');
        if (!file_exists($path)) {
            abort(404);
        }
        $html = file_get_contents($path);
        return response($html, 200)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function css()
    {
        $path = resource_path('views/cheque/styles.css');
        if (!file_exists($path)) {
            abort(404);
        }
        $css = file_get_contents($path);
        return response($css, 200)->header('Content-Type', 'text/css; charset=UTF-8');
    }

    // GET /api/branches
    public function branches()
    {
        try {
            $schema = DB::connection('pgsql')->getSchemaBuilder();
            if (!$schema->hasTable('branches')) {
                return response()->json([]);
            }
            return response()->json(Branch::query()->orderBy('code')->get());
        } catch (\Throwable $e) {
            return response()->json([]);
        }
    }

    // POST /api/branches
    public function branchesStore(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
        ]);

        try {
            $schema = DB::connection('pgsql')->getSchemaBuilder();
            if (!$schema->hasTable('branches')) {
                return response()->json(['error' => 'missing table'], 400);
            }

            // Check if branch exists
            $exists = Branch::where('code', $data['code'])->exists();
            if ($exists) {
                return response()->json(['error' => 'Branch code already exists'], 409);
            }

            $branch = Branch::create($data);
            return response()->json(['status' => 'ok', 'branch' => $branch], 201);
        } catch (\Throwable $e) {
            Log::error('Branch create error: ' . $e->getMessage());
            return response()->json(['error' => 'create failed'], 500);
        }
    }

    // DELETE /api/branches/{code}
    public function branchesDestroy($code)
    {
        try {
            $schema = DB::connection('pgsql')->getSchemaBuilder();
            if (!$schema->hasTable('branches')) {
                return response()->json(['ok' => true]);
            }

            Branch::where('code', $code)->delete();
            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            Log::error('Branch delete error: ' . $e->getMessage());
            return response()->json(['ok' => false], 500);
        }
    }

    // GET /api/cheques
    public function chequesIndex(Request $request)
    {
        try {
            $schema = DB::connection('pgsql')->getSchemaBuilder();
            if (!$schema->hasTable('cheques')) {
                return response()->json([]);
            }
            $q = trim((string) $request->query('q', ''));
            $query = Cheque::query()->orderByDesc('id');
            if ($q !== '') {
                $query->where(function ($w) use ($q) {
                    $w->where('payee', 'ilike', "%$q%")
                      ->orWhere('bank', 'ilike', "%$q%")
                      ->orWhere('branch_code', 'ilike', "%$q%")
                      ->orWhere('cheque_number', 'ilike', "%$q%")
                      ->orWhere('date', 'ilike', "%$q%");
                });
            }
            return response()->json($query->get());
        } catch (\Throwable $e) {
            return response()->json([]);
        }
    }

    // POST /api/cheques
    public function chequesStore(Request $request)
    {
        $data = $request->validate([
            'branch_code' => 'nullable|string|max:50',
            'bank' => 'required|string|max:50',
            'cheque_number' => 'required|string|max:50',
            'date' => 'required|date',
            'payee' => 'required|string|max:255',
            'amount' => 'required|numeric',
        ]);
        try {
            $schema = DB::connection('pgsql')->getSchemaBuilder();
            if (!$schema->hasTable('cheques')) {
                return response()->json(['error' => 'missing table'], 400);
            }
            $cheque = Cheque::create([
                'branch_code' => $data['branch_code'] ?? null,
                'bank' => $data['bank'],
                'cheque_number' => $data['cheque_number'],
                'date' => $data['date'],
                'payee' => $data['payee'],
                'amount' => $data['amount'],
                'printed_at' => now(),
            ]);
            $id = $cheque->id;
            return response()->json(['id' => $id], 201);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'db error'], 500);
        }
    }

    // DELETE /api/cheques/{id}
    public function chequesDestroy($id)
    {
        try {
            $schema = DB::connection('pgsql')->getSchemaBuilder();
            if (!$schema->hasTable('cheques')) {
                return response()->json(['ok' => true]);
            }
            Cheque::where('id', $id)->delete();
            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false], 500);
        }
    }

    // GET /api/cheques/next
    public function chequesNext()
    {
        try {
            $schema = DB::connection('pgsql')->getSchemaBuilder();
            if (!$schema->hasTable('cheques')) {
                return response()->json(['cheque_number' => '']);
            }
            $row = Cheque::query()->orderByDesc('id')->first();
            $last = $row->cheque_number ?? '';
            $next = $this->incrementCheque($last);
            return response()->json(['cheque_number' => $next]);
        } catch (\Throwable $e) {
            return response()->json(['cheque_number' => '']);
        }
    }

    private function incrementCheque(string $last): string
    {
        $t = trim($last);
        if ($t === '') return '';
        // extract trailing digits
        if (!preg_match('/^(.*?)(\d+)$/', $t, $m)) return $t;
        $prefix = $m[1]; $num = $m[2];
        $nlen = strlen($num);
        $next = (string)((int)$num + 1);
        $next = str_pad($next, $nlen, '0', STR_PAD_LEFT);
        return $prefix . $next;
    }

    // GET /api/templates
    public function templatesIndex()
    {
        try {
            $schema = DB::connection('pgsql')->getSchemaBuilder();
            if (!$schema->hasTable('cheque_templates')) {
                return response()->json([]);
            }
            return response()->json(ChequeTemplate::query()->orderByDesc('id')->get());
        } catch (\Throwable $e) {
            return response()->json([]);
        }
    }

    // POST /api/templates
    public function templatesStore(Request $request)
    {
        $data = $request->validate([
            'bank' => 'required|string|max:50',
            'template_json' => 'required|array',
        ]);

        try {
            $schema = DB::connection('pgsql')->getSchemaBuilder();
            if (!$schema->hasTable('cheque_templates')) {
                return response()->json(['error' => 'missing table'], 400);
            }

            // UPSERT: If bank exists, update; otherwise insert
            $template = ChequeTemplate::updateOrCreate(
                ['bank' => $data['bank']],
                ['template_json' => $data['template_json']]
            );

            return response()->json(['status' => 'ok', 'id' => $template->id]);
        } catch (\Throwable $e) {
            Log::error('Template save error: ' . $e->getMessage());
            return response()->json(['error' => 'save failed'], 500);
        }
    }

    // GET /api/payees - Autocomplete for payee names
    public function payees(Request $request)
    {
        try {
            $schema = DB::connection('pgsql')->getSchemaBuilder();
            if (!$schema->hasTable('cheques')) {
                return response()->json([]);
            }

            $q = trim($request->query('q', ''));
            $limit = (int) $request->query('limit', 10);
            $branch = trim($request->query('branch', ''));

            $query = DB::connection('pgsql')
                ->table('cheques')
                ->select('payee', DB::raw('COUNT(*) as cnt'))
                ->whereNotNull('payee')
                ->where('payee', '<>', '');

            if ($q !== '') {
                $query->where('payee', 'ilike', "%$q%");
            }

            if ($branch !== '') {
                $query->where('branch_code', $branch);
            }

            $results = $query->groupBy('payee')
                ->orderByDesc('cnt')
                ->orderBy('payee')
                ->limit($limit)
                ->get();

            return response()->json($results->pluck('payee'));
        } catch (\Throwable $e) {
            return response()->json([]);
        }
    }
}
