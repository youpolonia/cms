<?php

namespace App\Services;

use App\Models\ContentVersion;
use Illuminate\Support\Collection;
use Barryvdh\DomPDF\Facade\Pdf;
use League\Csv\Writer;

class VersionExporter
{
    public function export(Collection $versions, string $format): string
    {
        return match($format) {
            'csv' => $this->exportToCsv($versions),
            'json' => $this->exportToJson($versions),
            'pdf' => $this->exportToPdf($versions),
            default => throw new \InvalidArgumentException("Unsupported format: {$format}")
        };
    }

    private function exportToCsv(Collection $versions): string
    {
        $csv = Writer::createFromString('');
        $csv->insertOne(['ID', 'Title', 'Version', 'Status', 'Created At']);

        foreach ($versions as $version) {
            $csv->insertOne([
                $version->id,
                $version->content->title,
                $version->version_number,
                $version->status,
                $version->created_at
            ]);
        }

        $path = tempnam(sys_get_temp_dir(), 'versions_export_') . '.csv';
        file_put_contents($path, $csv->toString());
        return $path;
    }

    private function exportToJson(Collection $versions): string
    {
        $data = $versions->map(function($version) {
            return [
                'id' => $version->id,
                'title' => $version->content->title,
                'version' => $version->version_number,
                'content' => $version->content,
                'status' => $version->status,
                'created_at' => $version->created_at
            ];
        });

        $path = tempnam(sys_get_temp_dir(), 'versions_export_') . '.json';
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
        return $path;
    }

    private function exportToPdf(Collection $versions): string
    {
        $pdf = Pdf::loadView('exports.versions_pdf', [
            'versions' => $versions
        ]);

        $path = tempnam(sys_get_temp_dir(), 'versions_export_') . '.pdf';
        $pdf->save($path);
        return $path;
    }
}