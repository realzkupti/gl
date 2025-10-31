@extends('tailadmin.layouts.app')

@section('title', 'งบทดลอง (แบบธรรมดา)')
{{-- @ php($page = 'trial-balance-plain') --}}

@section('content')
<div class="p-4 md:p-6 2xl:p-10">
    <h1 class="text-2xl font-semibold mb-4">งบทดลอง (แบบธรรมดา)</h1>

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">งบทดลอง (แบบธรรมดา)</h2>
        <nav>
            <ol class="flex items-center gap-2">
                <li>
                    <a class="font-medium text-gray-700 hover:text-brand-500 dark:text-gray-400" href="{{ route('tailadmin.dashboard') }}">Dashboard /</a>
                </li>
                <li class="font-medium text-brand-500">งบทดลอง</li>
            </ol>
        </nav>
    </div>

    @if($selectedPeriod)
        <p class="mb-2 text-sm text-gray-600">
            แสดงงวดบัญชี: {{ $selectedPeriod->GLP_SEQUENCE }}/{{ $selectedPeriod->GLP_YEAR }}
            ({{ \Carbon\Carbon::parse($selectedPeriod->GLP_ST_DATE)->format('j M Y') }} - {{ \Carbon\Carbon::parse($selectedPeriod->GLP_EN_DATE)->format('j M Y') }})
        </p>
    @endif

    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 print:hidden">
    <form method="get" class="flex flex-col gap-4 md:flex-row md:items-end md:gap-6">
        <div class="flex-1">
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">งวดบัญชี</label>
            <select name="period" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                @foreach($periods ?? [] as $p)
                    <option value="{{ $p->GLP_KEY }}" {{ ($selectedPeriod && $selectedPeriod->GLP_KEY == $p->GLP_KEY) ? 'selected' : '' }}>
                        {{ $p->GLP_SEQUENCE }}/{{ $p->GLP_YEAR }} ({{ \Carbon\Carbon::parse($p->GLP_ST_DATE)->format('M Y') }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="submit" class="rounded-lg bg-blue-600 dark:bg-blue-500 px-6 py-2 text-white hover:bg-blue-700 dark:hover:bg-blue-600 transition font-medium shadow-sm">แสดงผล</button>
            <a href="{{ route('bplus.trial-balance.pdf', ['period' => $selectedPeriod?->GLP_KEY]) }}" target="_blank" class="inline-flex items-center rounded-lg bg-gray-700 dark:bg-gray-600 px-6 py-2 text-white hover:bg-gray-800 dark:hover:bg-gray-700 transition font-medium shadow-sm">พิมพ์ PDF</a>
            <a href="{{ route('bplus.trial-balance.excel', ['period' => $selectedPeriod?->GLP_KEY]) }}" class="inline-flex items-center rounded-lg bg-green-600 dark:bg-green-500 px-6 py-2 text-white hover:bg-green-700 dark:hover:bg-green-600 transition font-medium shadow-sm">Export Excel</a>
        </div>
    </form>
    </div>

    <!-- DataTables (striped rows) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <style>
        /* numeric columns: fixed width so DR/CR and balances align (no wrap) */
        .col-num { box-sizing: border-box; width:140px; min-width:120px; max-width:160px; text-align:right; white-space:nowrap; }
        /* text columns: allow wrapping so table expands by content (main table) */
        .col-text { min-width:120px; max-width:9999px; word-break:break-word; white-space:normal; }
        /* main table uses automatic layout so columns expand based on content; modal/detail tables keep fixed layout */
        #tb-trial { table-layout: auto; }
        #detail-table, #entries-table { table-layout: fixed; }
        /* keep type column in detail modal truncated to avoid overlapping numbers */
        #detail-table th.col-type, #detail-table td.col-type { max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        /* Fixed width for DR and CR columns in modal tables to ensure equal width */
        #detail-table th:nth-child(4), #detail-table td:nth-child(4),
        #detail-table th:nth-child(5), #detail-table td:nth-child(5),
        #entries-table th:nth-child(3), #entries-table td:nth-child(3),
        #entries-table th:nth-child(4), #entries-table td:nth-child(4) {
            width: 120px;
            min-width: 100px;
            max-width: 140px;
            text-align: right;
            white-space: nowrap;
        }
    </style>

    <style>
        /* small account column for Entries modal */
        .col-account { width:100px; min-width:80px; max-width:120px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    </style>
    <style>
        @media print { .print\:hidden { display:none !important; } }
        @page { size: A4 landscape; margin: 10mm; }
    </style>

    <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-auto">
        <table id="tb-trial" class="display stripe" style="width:100%">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-800">
                    <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 col-text text-gray-900 dark:text-white" rowspan="2">เลขบัญชี</th>
                    <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 col-text text-gray-900 dark:text-white" rowspan="2">ชื่อบัญชี</th>
                    <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-center text-gray-900 dark:text-white" colspan="2">ยอดยกมา</th>
                    <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-center text-gray-900 dark:text-white" colspan="2">ยอดเคลื่อนไหว</th>
                    <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-center text-gray-900 dark:text-white" colspan="2">ยอดคงเหลือ</th>
                </tr>
                <tr class="bg-gray-100 dark:bg-gray-800">
                    <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 col-num text-gray-900 dark:text-white">เดบิต</th>
                    <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 col-num text-gray-900 dark:text-white">เครดิต</th>
                    <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 col-num text-gray-900 dark:text-white">เดบิต</th>
                    <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 col-num text-gray-900 dark:text-white">เครดิต</th>
                    <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 col-num text-gray-900 dark:text-white">เดบิต</th>
                    <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 col-num text-gray-900 dark:text-white">เครดิต</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows ?? [] as $r)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="border border-gray-300 dark:border-gray-700 px-2 py-1 col-text text-gray-900 dark:text-gray-100" title="{{ $r['account_number'] }}">
                            <a href="#" class="detail-link underline text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300" data-account="{{ $r['account_number'] }}" data-account-name="{{ $r['account_name'] }}">{{ $r['account_number'] }}</a>
                        </td>
                        <td class="border border-gray-300 dark:border-gray-700 px-2 py-1 col-text text-gray-900 dark:text-gray-100" title="{{ $r['account_name'] }}">{{ $r['account_name'] }}</td>
                        <td class="border border-gray-300 dark:border-gray-700 px-2 py-1 col-num text-gray-900 dark:text-gray-100">{{ number_format($r['opening_debit'],2) }}</td>
                        <td class="border border-gray-300 dark:border-gray-700 px-2 py-1 col-num text-gray-900 dark:text-gray-100">{{ number_format($r['opening_credit'],2) }}</td>
                        <td class="border border-gray-300 dark:border-gray-700 px-2 py-1 col-num text-gray-900 dark:text-gray-100">{{ number_format($r['movement_debit'],2) }}</td>
                        <td class="border border-gray-300 dark:border-gray-700 px-2 py-1 col-num text-gray-900 dark:text-gray-100">{{ number_format($r['movement_credit'],2) }}</td>
                        <td class="border border-gray-300 dark:border-gray-700 px-2 py-1 col-num text-gray-900 dark:text-gray-100">{{ number_format($r['balance_debit'],2) }}</td>
                        <td class="border border-gray-300 dark:border-gray-700 px-2 py-1 col-num text-gray-900 dark:text-gray-100">{{ number_format($r['balance_credit'],2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @php
        // Build totals like PDF: opening, movement, balance for each group
        $mk = function(){ return ['o_dr'=>0,'o_cr'=>0,'m_dr'=>0,'m_cr'=>0,'b_dr'=>0,'b_cr'=>0]; };
        $total = $mk();
        $assets = $mk(); $liab = $mk(); $equity = $mk(); $revenue = $mk(); $expense = $mk();
        $add = function(&$t, $r){
            $t['o_dr'] += (float)($r['opening_debit'] ?? 0);
            $t['o_cr'] += (float)($r['opening_credit'] ?? 0);
            $t['m_dr'] += (float)($r['movement_debit'] ?? 0);
            $t['m_cr'] += (float)($r['movement_credit'] ?? 0);
            $t['b_dr'] += (float)($r['balance_debit'] ?? 0);
            $t['b_cr'] += (float)($r['balance_credit'] ?? 0);
        };
        foreach(($rows ?? []) as $r){
            $add($total, $r);
            $first = substr((string)($r['account_number'] ?? ''),0,1);
            if ($first==='1') $add($assets,$r);
            elseif ($first==='2') $add($liab,$r);
            elseif ($first==='3') $add($equity,$r);
            elseif ($first==='4') $add($revenue,$r);
            else $add($expense,$r);
        }
        $rowsSum = [
            'รวมทั้งหมด' => $total,
            'รวมสินทรัพย์' => $assets,
            'รวมหนี้สิน' => $liab,
            'รวมส่วนของเจ้าของ' => $equity,
            'รวมรายได้' => $revenue,
            'รวมค่าใช้จ่าย' => $expense,
        ];
        $net = function($t){ $d = ($t['b_dr'] ?? 0) - ($t['b_cr'] ?? 0); return [max($d,0), max(-$d,0)]; };
        $nets = [
            'รวมสินทรัพย์สุทธิ' => $net($assets),
            'รวมหนี้สินสุทธิ' => $net($liab),
            'รวมรายได้สุทธิ' => $net($revenue),
            'รวมค่าใช้จ่ายสุทธิ' => $net($expense),
        ];
    @endphp

    <div class="mt-6">
        <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">สรุปยอดท้ายงบทดลอง</h3>
        <div class="overflow-auto rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <table class="min-w-full border-collapse" style="width:100%">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800">
                        <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-gray-900 dark:text-white" colspan="2">รายการ</th>
                        <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-gray-900 dark:text-white" colspan="2">ยอดยกมา</th>
                        <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-gray-900 dark:text-white" colspan="2">ยอดเคลื่อนไหว</th>
                        <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-gray-900 dark:text-white" colspan="2">ยอดคงเหลือ</th>
                    </tr>
                    <tr class="bg-gray-100 dark:bg-gray-800">
                        <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-gray-900 dark:text-white" colspan="2"></th>
                        <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-900 dark:text-white">เดบิต</th>
                        <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-900 dark:text-white">เครดิต</th>
                        <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-900 dark:text-white">เดบิต</th>
                        <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-900 dark:text-white">เครดิต</th>
                        <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-900 dark:text-white">เดบิต</th>
                        <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-900 dark:text-white">เครดิต</th>
                    </tr>
                </thead>
                <tbody class="text-gray-900 dark:text-gray-100">
                    @php
                        // Prepare Profit/Loss from movement: (Revenue 4,7) - (Expenses 5,6,8,9)
                        $__revCats = ['4','7'];
                        $__expCats = ['5','6','8','9'];
                        $__rev = 0.0; $__exp = 0.0;
                        foreach(($rows ?? []) as $__r){
                            $__acc = (string)($__r['account_number'] ?? '');
                            $__cat = strlen($__acc) ? $__acc[0] : '';
                            $__mvDr = (float)($__r['movement_debit'] ?? 0);
                            $__mvCr = (float)($__r['movement_credit'] ?? 0);
                            if (in_array($__cat, $__revCats, true)) {
                                $__rev += ($__mvCr - $__mvDr);
                            } elseif (in_array($__cat, $__expCats, true)) {
                                $__exp += ($__mvDr - $__mvCr);
                            }
                        }
                        $__pl = $__rev - $__exp; // positive = profit (credit), negative = loss (debit)
                    @endphp
                    @foreach($rowsSum as $label => $t)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1" colspan="2"><strong>{{ $label }}</strong></td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">{{ number_format($t['o_dr'],2) }}</td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">{{ number_format($t['o_cr'],2) }}</td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">{{ number_format($t['m_dr'],2) }}</td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">{{ number_format($t['m_cr'],2) }}</td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">{{ number_format($t['b_dr'],2) }}</td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">{{ number_format($t['b_cr'],2) }}</td>
                        </tr>
                    @endforeach
                    @foreach($nets as $label => $pair)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1" colspan="2"><strong>{{ $label }}</strong></td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right"></td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right"></td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">{{ number_format($pair[0],2) }}</td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">{{ number_format($pair[1],2) }}</td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right"></td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right"></td>
                        </tr>
                        @if($label === 'รวมค่าใช้จ่ายสุทธิ')
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 font-semibold">
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1" colspan="2"><strong>กำไร/ขาดทุน</strong></td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right"></td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right"></td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right {{ $__pl < 0 ? 'text-red-600 dark:text-red-400' : '' }}">{{ number_format($__pl < 0 ? abs($__pl) : 0, 2) }}</td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right {{ $__pl > 0 ? 'text-green-600 dark:text-green-400' : '' }}">{{ number_format($__pl > 0 ? $__pl : 0, 2) }}</td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right"></td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right"></td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    </div>



    <!-- Modal -->
    <div id="detail-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:99999;">
        <div class="modal-scroll bg-white dark:bg-gray-900 rounded-lg shadow-2xl" style="padding:1.5rem; width:95%; max-width:1400px; margin:2rem auto; max-height:90vh; overflow:auto;">
            <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-200 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-900 z-20">
                <div class="flex flex-col gap-1">
                    <h3 id="detail-title" class="text-xl font-bold text-gray-900 dark:text-white">รายละเอียดบัญชี</h3>
                    <div id="detail-subheader" class="text-sm text-gray-600 dark:text-gray-400">
                        บัญชี: <span id="detail-account" class="font-semibold text-gray-900 dark:text-white"></span> —
                        <span id="detail-account-name" class="text-gray-700 dark:text-gray-300"></span> |
                        สาขา: <span id="detail-branch" class="text-gray-700 dark:text-gray-300"></span>
                    </div>
                </div>
                <button id="detail-close" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg transition">ปิด</button>
            </div>
            <div>
                <style>
                    /* prevent wrapping in modal tables */
                    #detail-table th, #detail-table td, #entries-table th, #entries-table td { white-space: nowrap; }
                    /* larger account text for detail header and entries links */
                    #detail-account { font-weight: 700; font-size: 1.05rem; }
                    .entries-account { font-weight:700; color: #1f2937; text-decoration: underline; cursor: pointer; }
                    /* constrain Type column to avoid overlapping numbers; show ellipsis */
                    th.col-type, td.col-type { max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
                    /* Fixed width for DR and CR columns in modal tables to ensure equal width */
                    #detail-table th:nth-child(4), #detail-table td:nth-child(4),
                    #detail-table th:nth-child(5), #detail-table td:nth-child(5),
                    #entries-table th:nth-child(3), #entries-table td:nth-child(3),
                    #entries-table th:nth-child(4), #entries-table td:nth-child(4) {
                        width: 120px;
                        min-width: 100px;
                        max-width: 140px;
                        text-align: right;
                        white-space: nowrap;
                    }
                    /* sticky table headers inside scrollable modal */
                    #detail-table thead th { position: sticky; top: var(--detail-head-top, 48px); z-index: 10; }
                    .dark #detail-table thead th { background: rgb(31, 41, 55); }
                    html:not(.dark) #detail-table thead th { background: rgb(243, 244, 246); }
                </style>
                <table id="detail-table" class="min-w-full display stripe" style="table; width:100%;">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-800">
                            <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-gray-900 dark:text-white">วันที่</th>
                            <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-gray-900 dark:text-white">เลขที่อ้างอิง</th>
                            <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 col-type text-gray-900 dark:text-white">ประเภทเอกสาร</th>
                            <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-900 dark:text-white">เดบิต</th>
                            <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-900 dark:text-white">เครดิต</th>
                            <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-gray-900 dark:text-white">ยอดคงเหลือ</th>
                            <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-gray-900 dark:text-white">สาขา</th>
                        </tr>
                    </thead>
                    <tbody id="detail-body" class="text-gray-900 dark:text-gray-100"></tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <!-- colspan = total columns (7) - 2 (DR/CR) = 5 -->
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 font-semibold text-gray-900 dark:text-white" colspan="3">รวมย่อย</td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 font-semibold text-right text-gray-900 dark:text-white" id="detail-sub-dr"></td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 font-semibold text-right text-gray-900 dark:text-white" id="detail-sub-cr"></td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 font-semibold text-gray-900 dark:text-white" colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Nested entries modal -->
    <div id="entries-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:99999;">
        <div class="modal-scroll bg-white dark:bg-gray-900 rounded-lg shadow-2xl" style="padding:1.5rem; width:95%; max-width:1400px; margin:2rem auto; max-height:90vh; overflow:auto;">
            <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-200 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-900 z-20">
                <h3 id="entries-title" class="text-xl font-bold text-gray-900 dark:text-white">รายการบันทึกบัญชี</h3>
                <button id="entries-close" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg transition">ปิด</button>
            </div>
                <div>
                <table id="entries-table" class="min-w-full display stripe" style="table-layout:fixed; width:100%;">
                    <style>
                        /* sticky table headers inside scrollable modal */
                        #entries-table thead th { position: sticky; top: var(--entries-head-top, 48px); z-index: 10; }
                        .dark #entries-table thead th { background: rgb(31, 41, 55); }
                        html:not(.dark) #entries-table thead th { background: rgb(243, 244, 246); }
                    </style>
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-800">
                            <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 col-account text-gray-900 dark:text-white">เลขบัญชี</th>
                            <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-gray-900 dark:text-white">ชื่อบัญชี</th>
                            <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-900 dark:text-white">เดบิต</th>
                            <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-900 dark:text-white">เครดิต</th>
                            <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-gray-900 dark:text-white">ที่มา</th>
                        </tr>
                    </thead>
                    <tbody id="entries-body" class="text-gray-900 dark:text-gray-100"></tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <!-- colspan = total columns (5) - 2 (DR/CR) = 3 -->
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 font-semibold text-gray-900 dark:text-white" colspan="2">รวม</td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 font-semibold text-right text-gray-900 dark:text-white" id="entries-sub-dr"></td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 font-semibold text-right text-gray-900 dark:text-white" id="entries-sub-cr"></td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 font-semibold text-gray-900 dark:text-white"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function(){
            var mainTable = $('#tb-trial').DataTable({
                paging: true,
                searching: true,
                info: true,
                autoWidth: false,
                columnDefs: [
                    { targets: [2,3,4,5,6,7], className: 'col-num', width: '140px' },
                    { targets: [0,1], className: 'col-text' }
                ]
            });

            // helper to load detail for an account and populate the detail modal
            function performDetailLoad(acc, acctName) {
                var period = $('select[name="period"]').val();
                $('#detail-modal').css('display','flex');
                try {
                    var dh = $('#detail-modal .modal-header').outerHeight() || 48;
                    $('#detail-modal .modal-scroll')[0].style.setProperty('--detail-head-top', dh + 'px');
                } catch(e) {}

                if ($.fn.DataTable.isDataTable('#detail-table')) {
                    try { $('#detail-table').DataTable().destroy(); } catch(e) {}
                }
                $('#detail-body').html('Loading...');
                $('#detail-sub-dr').text('');
                $('#detail-sub-cr').text('');

                // set header
                $('#detail-account').text(acc);
                $('#detail-account-name').text(acctName || '');
                $('#detail-branch').text('');

                $.getJSON('{{ route("bplus.trial-balance.detail") }}', { account: acc, period: period }, function(res){
                    var rows = res.data || [];
                    var opening = Number(res.opening || 0);
                    var running = opening; // start with opening balance
                    var html = '';
                    var sumDr = 0.0, sumCr = 0.0;
                    function fmtBal(x){
                        var v = Number(x||0);
                        var s = Math.abs(v).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
                        return v < 0 ? '('+ s +')' : s;
                    }
                    rows.forEach(function(r){
                        var dr = r.DR ? Number(r.DR) : 0;
                        var cr = r.CR ? Number(r.CR) : 0;
                        sumDr += dr; sumCr += cr;
                        var docRef = r.doc_ref || '';
                        var docType = r.doc_type || '';
                        var branchText = r.branch_code || '';
                        running += (dr - cr); // always DR-CR
                        html += '<tr class="hover:bg-gray-50 dark:hover:bg-gray-800">'+
                            '<td class="border border-gray-300 dark:border-gray-600 px-2 py-1">'+ r.doc_date +'</td>'+
                            '<td class="border border-gray-300 dark:border-gray-600 px-2 py-1"><a href="#" class="doc-link text-blue-600 dark:text-blue-400 hover:underline" title="'+ docRef.replace(/"/g,'&quot;') +'" data-doc_key="'+ r.doc_key +'" data-doc_ref="'+ docRef +'">'+ docRef +'</a></td>'+
                            '<td class="border border-gray-300 dark:border-gray-600 px-2 py-1 col-type" title="'+ docType.replace(/"/g,'&quot;') +'">'+ (docType) +'</td>'+
                            '<td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">'+ (dr ? dr.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) : '') +'</td>'+
                            '<td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">'+ (cr ? cr.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) : '') +'</td>'+
                            '<td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">'+ fmtBal(running) +'</td>'+
                            '<td class="border border-gray-300 dark:border-gray-600 px-2 py-1" title="'+ branchText.replace(/"/g,'&quot;') +'">'+ branchText +'</td>'+
                        '</tr>';
                    });

                    $('#detail-body').html(html);
                    if (rows.length > 0) {
                        var branches = rows.map(function(x){ return x.branch_code; }).filter(function(v,i,a){ return v && a.indexOf(v)===i; });
                        $('#detail-branch').text(branches.join(', '));
                    }
                    $('#detail-sub-dr').text(sumDr.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}));
                    // $('#detail-sub-dr').addClass('text-right');
                    $('#detail-sub-cr').text(sumCr.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}));
                    // $('#detail-sub-cr').addClass('text-right');

                    $('#detail-table').DataTable({ paging: false, searching: false, info: false, order: [[0, 'asc']] });
                });
            }

            // click handler for account number (open details)
            $(document).on('click', '.detail-link', function(e){
                e.preventDefault();
                var acc = $(this).data('account');
                var acctName = $(this).data('account-name') || $(this).data('accountName') || '';
                performDetailLoad(acc, acctName);
            });

            // single delegated handler for doc links (outside AJAX success) — avoids duplicate bindings
            $(document).off('click', '.doc-link').on('click', '.doc-link', function(ev){
                ev.preventDefault();
                var docKey = $(this).data('doc_key');
                var docRef = $(this).data('doc_ref') || docKey;
                $('#entries-body').html('Loading...');
                $('#entries-sub-dr').text('');
                $('#entries-sub-cr').text('');
                $('#entries-modal').css('display','flex');
                try {
                    var eh = $('#entries-modal .modal-header').outerHeight() || 48;
                    $('#entries-modal .modal-scroll')[0].style.setProperty('--entries-head-top', eh + 'px');
                } catch(e) {}

                // destroy entries table if exists
                if ($.fn.DataTable.isDataTable('#entries-table')) {
                    try { $('#entries-table').DataTable().destroy(); } catch(e) {}
                }

                var sumEntriesDr = 0.0, sumEntriesCr = 0.0;
                $.getJSON('{{ route("bplus.trial-balance.entries") }}', { doc_key: docKey }, function(res){
                    var rows = res.data || [];
                    var html = '';
                    rows.forEach(function(rr){
                        var edr = rr.DR ? Number(rr.DR) : 0;
                        var ecr = rr.CR ? Number(rr.CR) : 0;
                        sumEntriesDr += edr; sumEntriesCr += ecr;
                        var acctCode = rr.account_code || '';
                        var acctName = rr.account_name || '';
                        var docRemark = rr.doc_remark || '';
                        var docType = rr.doc_type || '';
                        html += '<tr class="hover:bg-gray-50 dark:hover:bg-gray-800">'+
                                '<td class="border border-gray-300 dark:border-gray-600 px-2 py-1 col-account"><a href="#" class="entries-account text-blue-600 dark:text-blue-400 hover:underline font-semibold" title="'+ acctCode.replace(/"/g,'&quot;') +'" data-account="'+ acctCode +'" data-account_name="'+ acctName +'">'+ acctCode +'</a></td>'+
                                '<td class="border border-gray-300 dark:border-gray-600 px-2 py-1" title="'+ acctName.replace(/"/g,'&quot;') +'">'+ acctName +'</td>'+
                                '<td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">'+ (edr ? edr.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) : '') +'</td>'+
                                '<td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">'+ (ecr ? ecr.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) : '') +'</td>'+
                                '<td class="border border-gray-300 dark:border-gray-600 px-2 py-1" title="'+ docRemark.replace(/"/g,'&quot;') +'">'+ (docRemark) +'</td>'+
                            '</tr>';
                    });
                    $('#entries-body').html(html);
                    // update entries subtotals
                    $('#entries-sub-dr').text(sumEntriesDr.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}));
                    $('#entries-sub-cr').text(sumEntriesCr.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}));

                    // set title with doc ref and doc type if available
                    var docType = (rows.length && rows[0].doc_type) ? rows[0].doc_type : '';
                    $('#entries-title').text('รายการบันทึกบัญชี — ' + docRef + (docType ? ' ('+docType+')' : ''));

                    $('#entries-table').DataTable({ paging: false, searching: false, info: false, order: [[0, 'asc']] });
                });
            });

            // delegated click: when clicking an account in entries, re-open detail modal for that account
            $(document).off('click', '.entries-account').on('click', '.entries-account', function(ev){
                ev.preventDefault();
                var acct = $(this).data('account');
                var acctName = $(this).data('account_name') || '';
                // close entries modal and load detail for the clicked account
                if ($.fn.DataTable.isDataTable('#entries-table')) { try { $('#entries-table').DataTable().destroy(); } catch(e) {} }
                $('#entries-modal').css('display','none');
                performDetailLoad(acct, acctName);
            });

            $('#detail-close').on('click', function(){
                // destroy detail table if exists and clear body
                if ($.fn.DataTable.isDataTable('#detail-table')) {
                    try { $('#detail-table').DataTable().destroy(); } catch(e) {}
                }
                $('#detail-body').html('');
                $('#detail-sub-dr').text('');
                $('#detail-sub-cr').text('');
                $('#detail-modal').css('display','none');
            });
            $('#entries-close').on('click', function(){
                if ($.fn.DataTable.isDataTable('#entries-table')) {
                    try { $('#entries-table').DataTable().destroy(); } catch(e) {}
                }
                $('#entries-body').html('');
                $('#entries-sub-dr').text('');
                $('#entries-sub-cr').text('');
                $('#entries-modal').css('display','none');
            });
        });
    </script>

    <p class="mt-4 text-sm text-gray-600">This is a plain server-rendered page (no Livewire) — useful for debugging DB queries and avoiding Livewire runtime dispatch issues.</p>
</div>

@if(!empty($error))
    @push('scripts')
    <script>
        if (window.showError) window.showError(@json($error));
    </script>
    @endpush
@endif

@push('styles')
<style>
    /* Hide legacy heading duplicated above TailAdmin header */
    h1.text-2xl.font-semibold.mb-4 { display: none; }
</style>
@endpush
{{-- moved: endsection at bottom --}}

@push('scripts')
<script>
// Sticky notes now handled by database-backed component below
</script>
@endpush

{{-- Sticky Note Component --}}
@if(isset($currentMenu) && $currentMenu && $currentMenu->has_sticky_note)
    <x-sticky-note
        :menu-id="$currentMenu->id"
        :company-id="session('current_company_id')"
    />
@endif

@endsection


