<?php

namespace App\Jobs;

use App\Models\AnalyticsExport;
use App\Models\Content;
use App\Services\ContentDiffService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessVersionComparisonExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $export;

    public function __construct(AnalyticsExport $export)
    {
        $this->export = $export;
    }

    public function handle()
    {
        try {
            $this->export->update(['status' => 'processing', 'progress' => 0]);

            // Generate export file
            $fileName = "exports/version-comparison-{$this->export->content_id}-".time().".{$this->export->format}";
            $content = $this->generateExportContent();

            Storage::put($fileName, $content);

            $this->export->update([
                'status' => 'completed',
                'file_path' => $fileName,
                'progress' => 100
            ]);
        } catch (Throwable $e) {
            $this->export->update([
                'status' => 'failed',
                'progress' => 0
            ]);
            
            logger()->error('Version comparison export failed', [
                'export_id' => $this->export->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    protected function generateExportContent()
    {
        $content = Content::with(['versions' => function($query) {
            $query->orderBy('created_at', 'desc');
        }])->findOrFail($this->export->content_id);

        $comparisons = [];
        $versions = $content->versions;
        
        // Compare each version with its previous version
        for ($i = 0; $i < count($versions) - 1; $i++) {
            $comparison = app(ContentDiffService::class)->compareVersions(
                $versions[$i]->content_text,
                $versions[$i+1]->content_text,
                [
                    'version1_id' => $versions[$i]->id,
                    'version2_id' => $versions[$i+1]->id,
                    'content_id' => $content->id
                ]
            );
            
            $comparisons[] = [
                'from_version' => $versions[$i]->id,
                'to_version' => $versions[$i+1]->id,
                'changes' => $comparison['changes'],
                'added' => $comparison['added'],
                'removed' => $comparison['removed'],
                'timestamp' => $versions[$i+1]->created_at,
                'author' => $versions[$i+1]->user->name
            ];
            
            $this->export->increment('progress', 90 / count($versions));
        }

        switch ($this->export->format) {
            case 'csv':
                return $this->generateCsv($comparisons);
            case 'json':
                return json_encode($comparisons, JSON_PRETTY_PRINT);
            case 'pdf':
                return $this->generatePdf($comparisons);
            default:
                throw new \InvalidArgumentException("Unsupported export format: {$this->export->format}");
        }
    }

    protected function generateCsv(array $comparisons): string
    {
        $output = fopen('php://temp', 'w');
        fputcsv($output, ['From Version', 'To Version', 'Changes', 'Added', 'Removed', 'Timestamp', 'Author']);
        
        foreach ($comparisons as $comparison) {
            fputcsv($output, [
                $comparison['from_version'],
                $comparison['to_version'],
                $comparison['changes'],
                $comparison['added'],
                $comparison['removed'],
                $comparison['timestamp'],
                $comparison['author']
            ]);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }

    protected function generatePdf(array $comparisons): string
    {
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('exports.version-comparison', [
            'comparisons' => $comparisons,
            'content' => Content::find($this->export->content_id)
        ]);
        
        return $pdf->output();
    }
}