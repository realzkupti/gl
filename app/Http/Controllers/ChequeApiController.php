<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

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
            if (!DB::getSchemaBuilder()->hasTable('branches')) {
                return response()->json([]);
            }
            $rows = DB::table('branches')->orderBy('code')->get();
            return response()->json($rows);
        } catch (\Throwable $e) {
            return response()->json([]);
        }
    }

    // GET /api/cheques
    public function chequesIndex(Request $request)
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('cheques')) {
                return response()->json([]);
            }
            $q = trim((string) $request->query('q', ''));
            $query = DB::table('cheques')->orderByDesc('id');
            if ($q !== '') {
                $query->where(function ($w) use ($q) {
                    $w->where('payee', 'ILIKE', "%$q%")
                      ->orWhere('bank', 'ILIKE', "%$q%")
                      ->orWhere('branch_code', 'ILIKE', "%$q%")
                      ->orWhere('cheque_number', 'ILIKE', "%$q%")
                      ->orWhere('date', 'ILIKE', "%$q%");
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
            if (!DB::getSchemaBuilder()->hasTable('cheques')) {
                return response()->json(['error' => 'missing table'], 400);
            }
            $id = DB::table('cheques')->insertGetId([
                'branch_code' => $data['branch_code'] ?? null,
                'bank' => $data['bank'],
                'cheque_number' => $data['cheque_number'],
                'date' => $data['date'],
                'payee' => $data['payee'],
                'amount' => $data['amount'],
                'printed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return response()->json(['id' => $id], 201);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'db error'], 500);
        }
    }

    // DELETE /api/cheques/{id}
    public function chequesDestroy($id)
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('cheques')) {
                return response()->json(['ok' => true]);
            }
            DB::table('cheques')->where('id', $id)->delete();
            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false], 500);
        }
    }

    // GET /api/cheques/next
    public function chequesNext()
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('cheques')) {
                return response()->json(['cheque_number' => '']);
            }
            $row = DB::table('cheques')->orderByDesc('id')->first();
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
}

