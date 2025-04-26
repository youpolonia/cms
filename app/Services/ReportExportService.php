<?php

namespace App\Services;

use App\Models\ReportTemplate;
use Dompdf\Dompdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportDataExport;

class ReportExportService
{
    public function export(ReportTemplate $template, string $format, array $data)
    {
        return match ($format) {
            'pdf' => $this->exportPdf($template, $data),
            'csv' => $this->exportCsv($template, $data),
            'excel' => $this->exportExcel($template, $data),
            default => throw new \InvalidArgumentException("Unsupported export format: {$format}"),
        };
    }

    protected function exportPdf(ReportTemplate $template, array $data)
    {
        $dompdf = new Dompdf();
        $html = view('exports.report', [
            'template' => $template,
            'data' => $data
        ])->render();
        
        $dompdf->loadHtml($html);
        $dompdf->render();
        
        return $dompdf->stream("report-{$template->id}.pdf");
    }

    protected function exportCsv(ReportTemplate $template, array $data)
    {
        return Excel::download(
            new ReportDataExport($template, $data),
            "report-{$template->id}.csv",
            \Maatwebsite\Excel\Excel::CSV
        );
    }

    protected function exportExcel(ReportTemplate $template, array $data)
    {
        return Excel::download(
            new ReportDataExport($template, $data),
            "report-{$template->id}.xlsx",
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    public function getSupportedFormats(): array
    {
        return ['pdf', 'csv', 'excel'];
    }
}