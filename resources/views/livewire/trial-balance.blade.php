<div>
    <style>
        /* Fixed width for DR and CR columns in modal table to ensure equal width */
        .detail-modal-table th:nth-child(4), .detail-modal-table td:nth-child(4),
        .detail-modal-table th:nth-child(5), .detail-modal-table td:nth-child(5) {
            width: 120px;
            min-width: 100px;
            max-width: 140px;
            text-align: right;
            white-space: nowrap;
        }
    </style>

    <div class="mb-4 flex items-center space-x-4">
        <div>
            <label>งวดบัญชี</label>
            <select wire:model="periodKey" class="border rounded px-2 py-1">
                @foreach($periods ?? [] as $p)
                    <option value="{{ $p->GLP_KEY }}">
                        {{ $p->GLP_SEQUENCE }}/{{ $p->GLP_YEAR }} ({{ \Carbon\Carbon::parse($p->GLP_ST_DATE)->format('M Y') }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label>สาขา</label>
            <select wire:model="branchId" class="border rounded px-2 py-1">
                <option value="">ทุกสาขา</option>
                @foreach($branches ?? [] as $b)
                    @php
                        $val = $b->code ?? $b->id ?? '';
                        $label = $b->name ?? $b->code ?? $b->id ?? '';
                    @endphp
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <button wire:click="load" class="bg-blue-600 text-white px-3 py-1 rounded">แสดงผล</button>
            <button type="button" onclick="(function(){
                var sel = document.querySelector('select[wire\\:model="periodKey"]');
                var companySel = document.querySelector('form select[name="company"]');
                var p = sel ? sel.value : '';
                var c = companySel ? companySel.value : '';
                var url = '/trial-balance-pdf?period=' + encodeURIComponent(p) + (c ? ('&company=' + encodeURIComponent(c)) : '');
                window.open(url, '_blank');
            })()" class="ml-2 bg-gray-700 text-white px-3 py-1 rounded print:hidden">พิมพ์ PDF</button>
            <button type="button" onclick="(function(){
                var sel = document.querySelector('select[wire\\:model=\"periodKey\"]');
                var p = sel ? sel.value : '';
                var url = '/trial-balance-excel?period=' + encodeURIComponent(p);
                window.location.href = url;
            })()" class="ml-2 bg-green-700 text-white px-3 py-1 rounded print:hidden">Export Excel</button>
        </div>
    </div>

    @php
        $selectedPeriod = collect($periods ?? [])->firstWhere('GLP_KEY', $periodKey);
    @endphp

    @if($selectedPeriod)
        <p class="mb-2 text-sm text-gray-600">
            แสดงงวดบัญชี: {{ $selectedPeriod->GLP_SEQUENCE }}/{{ $selectedPeriod->GLP_YEAR }}
            ({{ \Carbon\Carbon::parse($selectedPeriod->GLP_ST_DATE)->format('j M Y') }} - {{ \Carbon\Carbon::parse($selectedPeriod->GLP_EN_DATE)->format('j M Y') }})
        </p>
    @endif

    <!-- Summary: Profit/Loss (movement only) -->
    @php
        $revCats = ['4','7'];
        $expCats = ['5','6','8','9'];
        $totalRevenue = 0.0; $totalExpense = 0.0;
        foreach(($rows ?? []) as $r){
            $acc = (string)($r['account_number'] ?? '');
            $cat = strlen($acc) ? $acc[0] : '';
            $mvDr = (float)($r['movement_debit'] ?? 0);
            $mvCr = (float)($r['movement_credit'] ?? 0);
            if (in_array($cat, $revCats, true)) {
                $totalRevenue += ($mvCr - $mvDr);
            } elseif (in_array($cat, $expCats, true)) {
                $totalExpense += ($mvDr - $mvCr);
            }
        }
        $profitLoss = $totalRevenue - $totalExpense;
    @endphp
    <div class="mt-2 mb-4 p-3 border rounded bg-white max-w-xl">
        <h3 class="text-lg font-semibold mb-2">สรุปกำไร/ขาดทุน</h3>
        <div class="grid grid-cols-2 gap-y-1">
            <div>รายได้ (หมวด 4, 7)</div>
            <div class="text-right">{{ number_format($totalRevenue, 2) }}</div>
            <div>ค่าใช้จ่าย (หมวด 5, 6, 8, 9)</div>
            <div class="text-right">{{ number_format($totalExpense, 2) }}</div>
            <div class="font-semibold">กำไร / (ขาดทุน)</div>
            <div class="text-right font-semibold">{{ number_format($profitLoss, 2) }}</div>
        </div>
    </div>

    <div class="overflow-auto">
        <table class="min-w-full border-collapse">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-2 py-1" rowspan="2">เลขบัญชี</th>
                    <th class="border px-2 py-1" rowspan="2">ชื่อบัญชี</th>
                    <th class="border px-2 py-1 text-center" colspan="2">ยอดยกมา</th>
                    <th class="border px-2 py-1 text-center" colspan="2">ยอดเคลื่อนไหว</th>
                    <th class="border px-2 py-1 text-center" colspan="2">ยอดคงเหลือ</th>
                </tr>
                <tr class="bg-gray-100">
                    <th class="border px-2 py-1 text-right">เดบิต</th>
                    <th class="border px-2 py-1 text-right">เครดิต</th>
                    <th class="border px-2 py-1 text-right">เดบิต</th>
                    <th class="border px-2 py-1 text-right">เครดิต</th>
                    <th class="border px-2 py-1 text-right">เดบิต</th>
                    <th class="border px-2 py-1 text-right">เครดิต</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $currentBranch = null;
                @endphp
                @foreach($rows ?? [] as $r)
                    @if($currentBranch !== $r['branch_id'])
                        @php $currentBranch = $r['branch_id']; @endphp
                        <tr class="bg-gray-200">
                            <td class="border px-2 py-1" colspan="8"><strong>สาขา: {{ $r['branch_name'] }}</strong></td>
                        </tr>
                    @endif
                    <tr>
                        <td class="border px-2 py-1">
                            <a href="#" wire:click.prevent="showMovementDetail('{{ $r['account_number'] }}', '{{ $r['branch_id'] }}')" class="underline text-blue-700">
                                {{ $r['account_number'] }}
                            </a>
                        </td>
                        <td class="border px-2 py-1">{{ $r['account_name'] }}</td>
                        <td class="border px-2 py-1 text-right">{{ number_format($r['opening_debit'], 2) }}</td>
                        <td class="border px-2 py-1 text-right">{{ number_format($r['opening_credit'], 2) }}</td>
                        <td class="border px-2 py-1 text-right">{{ number_format($r['movement_debit'], 2) }}</td>
                        <td class="border px-2 py-1 text-right">{{ number_format($r['movement_credit'], 2) }}</td>
                        <td class="border px-2 py-1 text-right">{{ number_format($r['balance_debit'], 2) }}</td>
                        <td class="border px-2 py-1 text-right">{{ number_format($r['balance_credit'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @php
        $sum = function(array $rows){
            $tot = [
                'all' => ['dr'=>0.0,'cr'=>0.0],
                'assets'=>['dr'=>0.0,'cr'=>0.0],
                'liab'=>['dr'=>0.0,'cr'=>0.0],
                'equity'=>['dr'=>0.0,'cr'=>0.0],
                'revenue'=>['dr'=>0.0,'cr'=>0.0],
                'expense'=>['dr'=>0.0,'cr'=>0.0],
            ];
            foreach ($rows as $r){
                $acc = (string)($r['account_number'] ?? '');
                $first = substr($acc,0,1);
                if ($first==='1') $g='assets';
                elseif ($first==='2') $g='liab';
                elseif ($first==='3') $g='equity';
                elseif ($first==='4') $g='revenue';
                else $g='expense';
                $bd = (float)($r['balance_debit'] ?? 0);
                $bc = (float)($r['balance_credit'] ?? 0);
                $tot['all']['dr'] += $bd; $tot['all']['cr'] += $bc;
                $tot[$g]['dr'] += $bd; $tot[$g]['cr'] += $bc;
            }
            return $tot;
        };
        $totals = $sum($rows ?? []);
    @endphp
    <div class="mt-4">
        <h3 class="font-semibold mb-2">สรุปยอดท้ายงบทดลอง</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            <div>รวมทั้งหมด: เดบิต {{ number_format($totals['all']['dr'] ?? 0,2) }} | เครดิต {{ number_format($totals['all']['cr'] ?? 0,2) }}</div>
            <div>รวมสินทรัพย์: เดบิต {{ number_format($totals['assets']['dr'] ?? 0,2) }} | เครดิต {{ number_format($totals['assets']['cr'] ?? 0,2) }}</div>
            <div>รวมหนี้สิน: เดบิต {{ number_format($totals['liab']['dr'] ?? 0,2) }} | เครดิต {{ number_format($totals['liab']['cr'] ?? 0,2) }}</div>
            <div>รวมส่วนของเจ้าของ: เดบิต {{ number_format($totals['equity']['dr'] ?? 0,2) }} | เครดิต {{ number_format($totals['equity']['cr'] ?? 0,2) }}</div>
            <div>รวมรายได้: เดบิต {{ number_format($totals['revenue']['dr'] ?? 0,2) }} | เครดิต {{ number_format($totals['revenue']['cr'] ?? 0,2) }}</div>
            <div>รวมค่าใช้จ่าย: เดบิต {{ number_format($totals['expense']['dr'] ?? 0,2) }} | เครดิต {{ number_format($totals['expense']['cr'] ?? 0,2) }}</div>
        </div>
    </div>

    <!-- Simple modal for details -->
    @if($showDetail ?? false)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white rounded shadow-lg w-11/12 max-w-4xl p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">รายละเอียดเคลื่อนไหว - {{ $detailAccount ?? '' }}</h3>
                    <button wire:click="closeDetail" class="px-2 py-1 bg-gray-200 rounded">ปิด</button>
                </div>

                <div class="overflow-auto max-h-96">
                    <table class="min-w-full detail-modal-table">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border px-2 py-1">วันที่</th>
                                <th class="border px-2 py-1">เลขที่อ้างอิง</th>
                                <th class="border px-2 py-1">รายการ</th>
                                <th class="border px-2 py-1 text-right">เดบิต</th>
                                <th class="border px-2 py-1 text-right">เครดิต</th>
                                <th class="border px-2 py-1">สาขา</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detailRows ?? [] as $d)
                                <tr>
                                    <td class="border px-2 py-1">{{ $d->doc_date ?? $d->date }}</td>
                                    <td class="border px-2 py-1">{{ $d->doc_key ?? $d->reference }}</td>
                                    <td class="border px-2 py-1">{{ $d->doc_type ?? $d->description }}</td>
                                    <td class="border px-2 py-1 text-right">{{ isset($d->DR) ? number_format($d->DR,2) : ($d->dc === 'D' ? number_format($d->amount,2) : '') }}</td>
                                    <td class="border px-2 py-1 text-right">{{ isset($d->CR) ? number_format($d->CR,2) : ($d->dc === 'C' ? number_format($d->amount,2) : '') }}</td>
                                    <td class="border px-2 py-1">{{ $d->branch_code ?? $d->branch_name }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
