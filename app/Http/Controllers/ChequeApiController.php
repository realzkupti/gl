<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Branch;
use App\Models\Cheque;
use App\Models\ChequeTemplate;

/**
 * ChequeApiController
 *
 * Handles API endpoints for cheque management.
 * Uses dynamic database connection from CompanyManager.
 * Follows MVC pattern with proper model usage and response formatting.
 */
class ChequeApiController extends Controller
{
    /**
     * Get all branches
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function branches()
    {
        try {
            $branches = Branch::orderBy('code')->get();
            return response()->json($branches);
        } catch (\Throwable $e) {
            Log::error('Failed to fetch branches: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch branches'], 500);
        }
    }

    /**
     * Create new branch
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function branchesStore(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:branches,code',
            'name' => 'required|string|max:255',
        ]);

        try {
            $branch = Branch::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Branch created successfully',
                'data' => $branch
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Failed to create branch: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create branch',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete branch by code
     *
     * @param string $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function branchesDestroy($code)
    {
        try {
            $deleted = Branch::where('code', $code)->delete();

            return response()->json([
                'success' => true,
                'message' => $deleted ? 'Branch deleted successfully' : 'Branch not found'
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to delete branch: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete branch'
            ], 500);
        }
    }

    /**
     * Get all cheques with optional search
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function chequesIndex(Request $request)
    {
        try {
            $search = trim($request->query('q', ''));

            $query = Cheque::query()
                ->with('branch') // Eager load relationship if exists
                ->orderByDesc('id');

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('payee', 'ilike', "%{$search}%")
                      ->orWhere('bank', 'ilike', "%{$search}%")
                      ->orWhere('branch_code', 'ilike', "%{$search}%")
                      ->orWhere('cheque_number', 'ilike', "%{$search}%")
                      ->orWhere('date', 'ilike', "%{$search}%");
                });
            }

            $cheques = $query->get();

            return response()->json($cheques);
        } catch (\Throwable $e) {
            Log::error('Failed to fetch cheques: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch cheques',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new cheque record
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function chequesStore(Request $request)
    {
        $validated = $request->validate([
            'branch_code' => 'nullable|string|max:50',
            'bank' => 'required|string|max:50',
            'cheque_number' => 'required|string|max:50|unique:sys_cheques,cheque_number',
            'date' => 'required|date',
            'payee' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
        ]);

        try {
            $cheque = Cheque::create([
                'branch_code' => $validated['branch_code'] ?? null,
                'bank' => $validated['bank'],
                'cheque_number' => $validated['cheque_number'],
                'date' => $validated['date'],
                'payee' => $validated['payee'],
                'amount' => $validated['amount'],
                'printed_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cheque saved successfully',
                'id' => $cheque->id,
                'data' => $cheque
            ], 201);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error saving cheque: ' . $e->getMessage());

            // Check if it's a unique constraint violation
            if (str_contains($e->getMessage(), 'unique') || str_contains($e->getMessage(), 'duplicate')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cheque number already exists'
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'Database error occurred',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        } catch (\Throwable $e) {
            Log::error('Failed to save cheque: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to save cheque',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Delete cheque by ID
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function chequesDestroy($id)
    {
        try {
            $cheque = Cheque::find($id);

            if (!$cheque) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cheque not found'
                ], 404);
            }

            $cheque->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cheque deleted successfully'
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to delete cheque: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete cheque'
            ], 500);
        }
    }

    /**
     * Get cheque by number
     *
     * @param string $number
     * @return \Illuminate\Http\JsonResponse
     */
    public function chequesByNumber($number)
    {
        try {
            $cheque = Cheque::where('cheque_number', $number)->first();

            if (!$cheque) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cheque not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $cheque
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to fetch cheque by number: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch cheque'
            ], 500);
        }
    }

    /**
     * Get next cheque number
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function chequesNext(Request $request)
    {
        try {
            $branch = $request->query('branch');

            $query = Cheque::query()->orderByDesc('id');

            // Filter by branch if provided
            if ($branch) {
                $query->where('branch_code', $branch);
            }

            $lastCheque = $query->first();
            $lastNumber = $lastCheque->cheque_number ?? '';
            $nextNumber = $this->incrementChequeNumber($lastNumber);

            return response()->json([
                'success' => true,
                'cheque_number' => $nextNumber
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to get next cheque number: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'cheque_number' => '',
                'message' => 'Failed to get next cheque number'
            ], 500);
        }
    }

    /**
     * Increment cheque number (e.g., "CH0001" => "CH0002")
     *
     * @param string $last
     * @return string
     */
    private function incrementChequeNumber(string $last): string
    {
        $trimmed = trim($last);

        if (empty($trimmed)) {
            return '0000001'; // Default first cheque number
        }

        // Extract prefix and trailing digits (e.g., "CH0001" => prefix="CH", num="0001")
        if (!preg_match('/^(.*?)(\d+)$/', $trimmed, $matches)) {
            return $trimmed; // No digits found, return as-is
        }

        $prefix = $matches[1];
        $number = $matches[2];
        $numberLength = strlen($number);

        // Increment and pad with zeros
        $nextNumber = (string)((int)$number + 1);
        $nextNumber = str_pad($nextNumber, $numberLength, '0', STR_PAD_LEFT);

        return $prefix . $nextNumber;
    }

    /**
     * Get all cheque templates
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function templatesIndex()
    {
        try {
            $templates = ChequeTemplate::orderByDesc('id')->get();

            return response()->json($templates);
        } catch (\Throwable $e) {
            Log::error('Failed to fetch templates: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch templates'
            ], 500);
        }
    }

    /**
     * Create or update cheque template
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function templatesStore(Request $request)
    {
        $validated = $request->validate([
            'bank' => 'required|string|max:50',
            'template_json' => 'required|array',
        ]);

        try {
            // Upsert: Update if exists, create if not
            $template = ChequeTemplate::updateOrCreate(
                ['bank' => $validated['bank']],
                ['template_json' => $validated['template_json']]
            );

            return response()->json([
                'success' => true,
                'message' => 'Template saved successfully',
                'id' => $template->id,
                'data' => $template
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to save template: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to save template'
            ], 500);
        }
    }

    /**
     * Get autocomplete suggestions for payee names
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function payees(Request $request)
    {
        try {
            $search = trim($request->query('q', ''));
            $limit = (int) $request->query('limit', 10);
            $branch = trim($request->query('branch', ''));

            $query = Cheque::select('payee', DB::raw('COUNT(*) as count'))
                ->whereNotNull('payee')
                ->where('payee', '<>', '')
                ->groupBy('payee')
                ->orderByDesc('count')
                ->limit($limit);

            if ($search !== '') {
                $query->where('payee', 'ilike', "%{$search}%");
            }

            if ($branch !== '') {
                $query->where('branch_code', $branch);
            }

            $results = $query->get();
            $payees = $results->pluck('payee');

            return response()->json($payees);
        } catch (\Throwable $e) {
            Log::error('Failed to fetch payees: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payees'
            ], 500);
        }
    }
}
