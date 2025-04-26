<?php

namespace App\Exports;

use App\Models\ReportTemplate;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportDataExport implements FromArray, WithHeadings, WithMapping, WithStyles
{
    protected $template;
    protected $data;

    public function __construct(ReportTemplate $template, array $data)
    {
        $this->template = $template;
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return array_column($this->template->fields, 'label');
    }

    public function map($row): array
    {
        return array_map(function($field) use ($row) {
            return $row[$field['name']] ?? '';
        }, $this->template->fields);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}