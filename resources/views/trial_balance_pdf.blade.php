@php
    $fmt = fn($n) => is_numeric($n) && (float)$n != 0.0 ? number_format($n, 2) : '';
    $periodText = function($p) {
        try {
            return \Carbon\Carbon::parse($p->GLP_ST_DATE)->format('j M Y') . ' - ' . \Carbon\Carbon::parse($p->GLP_EN_DATE)->format('j M Y');
        } catch (Exception $e) {
            return ($p->GLP_ST_DATE ?? '') . ' - ' . ($p->GLP_EN_DATE ?? '');
        }
    };
    // $fontReg = file_exists(public_path('fonts/Sarabun-Regular.ttf')) ? public_path('fonts/Sarabun-Regular.ttf') : resource_path('fonts/Sarabun-Regular.ttf');
    // $fontBold = file_exists(public_path('fonts/Sarabun-Bold.ttf')) ? public_path('fonts/Sarabun-Bold.ttf') : resource_path('fonts/Sarabun-Bold.ttf');
@endphp

<!doctype html>
<html>
<head>
    <meta charset="utf-8"/>
    <style>
        /* @page { size: A4 landscape; margin: 10mm; } */
        body { font-family: sarabun, DejaVu Sans, sans-serif; font-size: 10pt; color: #111; }
        h1 { font-size: 16pt; margin: 0 0 6px 0; }
        .meta { font-size: 10pt; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        /* Let mPDF handle table header/body rendering to prevent odd paging */
        /* thead { display: table-header-group; } */
        /* tbody { display: table-row-group; } */
        th, td { border: 1px solid #bbb; padding: 3px 5px; font-size: 10pt; }
        th { background: #f0f0f0; text-align: left; }
        td, th { word-wrap: break-word; }
        .num { text-align: right; }
        .summary { margin-top: 12px; width: 60%; }
        .summary th, .summary td { border: none; }
        .summary td.label { text-align: left; }
        .summary td.num { border-bottom: 1px solid #ddd; }
    </style>
</head>
<body>
    <h1>งบทดลอง</h1>
    <div class="meta">
        บริษัท : {{ $company }}<br>
        งวดบัญชี: {{ $period->GLP_SEQUENCE }}/{{ $period->GLP_YEAR }} ({{ $periodText($period) }})
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width:12%">เลขบัญชี</th>
                <th rowspan="2" style="width:28%">ชื่อบัญชี</th>
                <th colspan="2" class="num" style="text-align:center">ยอดยกมา</th>
                <th colspan="2" class="num" style="text-align:center">ยอดเคลื่อนไหว</th>
                <th colspan="2" class="num" style="text-align:center">ยอดคงเหลือ</th>
            </tr>
            <tr>
                <th class="num" style="width:10%">เดบิต</th>
                <th class="num" style="width:10%">เครดิต</th>
                <th class="num" style="width:10%">เดบิต</th>
                <th class="num" style="width:10%">เครดิต</th>
                <th class="num" style="width:10%">เดบิต</th>
                <th class="num" style="width:10%">เครดิต</th>
            </tr>
        </thead>
        <tbody>
            @foreach(($rows ?? []) as $r)
                <tr>
                    <td>{{ $r['account_number'] }}</td>
                    <td>{{ $r['account_name'] }}</td>
                    <td class="num">{{ $fmt($r['opening_debit']) }}</td>
                    <td class="num">{{ $fmt($r['opening_credit']) }}</td>
                    <td class="num">{{ $fmt($r['movement_debit']) }}</td>
                    <td class="num">{{ $fmt($r['movement_credit']) }}</td>
                    <td class="num">{{ $fmt($r['balance_debit']) }}</td>
                    <td class="num">{{ $fmt($r['balance_credit']) }}</td>
                </tr>
            @endforeach
        </tbody>
        @php
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
            foreach (($rows ?? []) as $r){
                $add($total, $r);
                $first = substr((string)($r['account_number'] ?? ''), 0, 1);
                if ($first==='1') $add($assets,$r);
                elseif($first==='2') $add($liab,$r);
                elseif($first==='3') $add($equity,$r);
                elseif($first==='4') $add($revenue,$r);
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
        @endphp
        <tfoot>
            @foreach($rowsSum as $label => $t)
                <tr>
                    <td></td>
                    <td style="font-weight:bold">{{ $label }}</td>
                    <td class="num" style="font-weight:bold">{{ $fmt($t['o_dr']) }}</td>
                    <td class="num" style="font-weight:bold">{{ $fmt($t['o_cr']) }}</td>
                    <td class="num" style="font-weight:bold">{{ $fmt($t['m_dr']) }}</td>
                    <td class="num" style="font-weight:bold">{{ $fmt($t['m_cr']) }}</td>
                    <td class="num" style="font-weight:bold">{{ $fmt($t['b_dr']) }}</td>
                    <td class="num" style="font-weight:bold">{{ $fmt($t['b_cr']) }}</td>
                </tr>
            @endforeach
            @php
                $net = function($t){ $d = ($t['b_dr'] ?? 0) - ($t['b_cr'] ?? 0); return [max($d,0), max(-$d,0)]; };
                $nets = [
                    'รวมสินทรัพย์สุทธิ' => $net($assets),
                    'รวมหนี้สินสุทธิ' => $net($liab),
                    'รวมรายได้สุทธิ' => $net($revenue),
                    'รวมค่าใช้จ่ายสุทธิ' => $net($expense),
                ];
            @endphp
            @foreach($nets as $label => $pair)
                <tr>
                    <td colspan="6" style="font-weight:bold">{{ $label }}</td>
                    <td class="num" style="font-weight:bold">{{ $fmt($pair[0]) }}</td>
                    <td class="num" style="font-weight:bold">{{ $fmt($pair[1]) }}</td>
                </tr>
            @endforeach
        </tfoot>
    </table>
</body>
</html>
