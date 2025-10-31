@php
    $fmt = fn($n) => is_numeric($n) && (float)$n != 0.0 ? number_format($n, 2) : '';
    $periodText = function($p) {
        try {
            return \Carbon\Carbon::parse($p->GLP_ST_DATE)->format('j M Y') . ' - ' . \Carbon\Carbon::parse($p->GLP_EN_DATE)->format('j M Y');
        } catch (Exception $e) {
            return ($p->GLP_ST_DATE ?? '') . ' - ' . ($p->GLP_EN_DATE ?? '');
        }
    };
@endphp

<!doctype html>
<html>
<head>
    <meta charset="utf-8"/>
    <style>
        body { font-family: sarabun, DejaVu Sans, sans-serif; font-size: 10pt; color: #111; }
        h1 { font-size: 16pt; margin: 0 0 6px 0; }
        .meta { font-size: 10pt; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #bbb; padding: 3px 5px; font-size: 10pt; }
        th { background: #f0f0f0; text-align: left; }
        td, th { word-wrap: break-word; }
        .num { text-align: right; }
        .subtotal { background: #e8e8e8; font-weight: bold; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    @foreach(($pages ?? []) as $pageIndex => $pageRows)
        @if($pageIndex > 0)
            <div class="page-break"></div>
        @endif

        <h1>งบทดลอง</h1>
        <div class="meta">
            บริษัท : {{ $company }}<br>
            งวดบัญชี: {{ $period->GLP_SEQUENCE }}/{{ $period->GLP_YEAR }} ({{ $periodText($period) }})<br>
            หน้า {{ $pageIndex + 1 }} / {{ count($pages) }}
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
                @foreach($pageRows as $r)
                    @php
                        $isSubtotal = isset($r['is_subtotal']) && $r['is_subtotal'];
                    @endphp
                    <tr class="{{ $isSubtotal ? 'subtotal' : '' }}">
                        <td>{{ $r['account_number'] }}</td>
                        <td>{{ $r['account_name'] }}</td>
                        <td class="num">{{ $r['opening_debit'] !== '' ? $fmt($r['opening_debit']) : '' }}</td>
                        <td class="num">{{ $r['opening_credit'] !== '' ? $fmt($r['opening_credit']) : '' }}</td>
                        <td class="num">{{ $r['movement_debit'] !== '' ? $fmt($r['movement_debit']) : '' }}</td>
                        <td class="num">{{ $r['movement_credit'] !== '' ? $fmt($r['movement_credit']) : '' }}</td>
                        <td class="num">{{ $fmt($r['balance_debit']) }}</td>
                        <td class="num">{{ $fmt($r['balance_credit']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($pageIndex == count($pages) - 1)
            {{-- Show grand total on last page --}}
            <table style="width: 60%; margin-top: 10px;">
                <tr style="background: #f9f9f9; font-weight: bold;">
                    <td style="width: 60%; padding: 5px;">รวมทั้งหมด</td>
                    <td class="num" style="padding: 5px;">{{ $fmt($totals['all']['dr']) }}</td>
                    <td class="num" style="padding: 5px;">{{ $fmt($totals['all']['cr']) }}</td>
                </tr>
            </table>
        @endif
    @endforeach
</body>
</html>
