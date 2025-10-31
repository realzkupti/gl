<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TrialBalanceExport implements FromArray, WithHeadings, WithStyles, WithCustomCsvSettings
{
    protected array $rows;
    protected array $totals;
    protected string $title;

    public function __construct(array $rows, array $totals, string $title = 'งบทดลอง')
    {
        $this->rows = $rows;
        $this->totals = $totals;
        $this->title = $title;
    }

    public function array(): array
    {
        $out = [];
        // Heading row 1 (merged visually by styling later)
        $out[] = [$this->title];
        $out[] = [];

        // Table header (two rows)
        $out[] = ['เลขบัญชี','ชื่อบัญชี','ยอดยกมา','', 'ยอดเคลื่อนไหว','', 'ยอดคงเหลือ',''];
        $out[] = ['', '', 'เดบิต','เครดิต','เดบิต','เครดิต','เดบิต','เครดิต'];

        // Data rows
        foreach ($this->rows as $r) {
            $out[] = [
                $r['account_number'] ?? '',
                $r['account_name'] ?? '',
                $r['opening_debit'] ?? 0,
                $r['opening_credit'] ?? 0,
                $r['movement_debit'] ?? 0,
                $r['movement_credit'] ?? 0,
                $r['balance_debit'] ?? 0,
                $r['balance_credit'] ?? 0,
            ];
        }

        // Blank line then totals
        $out[] = [];
        $out[] = ['รวมทั้งหมด', '', $this->totals['all']['dr'] ?? 0, $this->totals['all']['cr'] ?? 0];
        $out[] = ['รวมสินทรัพย์', '', $this->totals['assets']['dr'] ?? 0, $this->totals['assets']['cr'] ?? 0];
        $out[] = ['รวมหนี้สิน', '', $this->totals['liab']['dr'] ?? 0, $this->totals['liab']['cr'] ?? 0];
        $out[] = ['รวมส่วนของเจ้าของ', '', $this->totals['equity']['dr'] ?? 0, $this->totals['equity']['cr'] ?? 0];
        $out[] = ['รวมรายได้', '', $this->totals['revenue']['dr'] ?? 0, $this->totals['revenue']['cr'] ?? 0];
        $out[] = ['รวมค่าใช้จ่าย', '', $this->totals['expense']['dr'] ?? 0, $this->totals['expense']['cr'] ?? 0];

        return $out;
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        // Bold title and headers
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A3:H4')->getFont()->setBold(true);
        // Numeric columns formatting
        $sheet->getStyle('C5:H1048576')->getNumberFormat()->setFormatCode('#,##0.00');
        return [];
    }

    public function getCsvSettings(): array
    {
        return [
            'use_bom' => true, // ensure Excel (Windows) reads UTF-8 Thai correctly
            'input_encoding' => 'UTF-8',
            'delimiter' => ',',
        ];
    }
}
