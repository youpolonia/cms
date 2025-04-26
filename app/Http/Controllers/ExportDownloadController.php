<?php

namespace App\Http\Controllers;

use App\Models\ExportHistory;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportDownloadController extends Controller
{
    public function download($historyId)
    {
        $history = ExportHistory::findOrFail($historyId);
        
        // Check permissions
        if (!auth()->user()->can('download', $history)) {
            abort(403, 'Unauthorized action.');
        }

        // Validate file exists
        if (!Storage::exists($history->file_path)) {
            abort(404, 'File not found');
        }

        // Track download
        $history->increment('download_count');
        $history->downloads()->create([
            'user_id' => auth()->id(),
            'downloaded_at' => now()
        ]);

        return Storage::download(
            $history->file_path,
            $this->generateFilename($history),
            ['Content-Type' => $this->getMimeType($history->file_path)]
        );
    }

    private function generateFilename(ExportHistory $history)
    {
        $extension = pathinfo($history->file_path, PATHINFO_EXTENSION);
        $date = $history->started_at->format('Y-m-d');
        return "{$history->template->name}_{$date}.{$extension}";
    }

    private function getMimeType(string $path)
    {
        return [
            'csv' => 'text/csv',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'pdf' => 'application/pdf',
            'json' => 'application/json',
        ][pathinfo($path, PATHINFO_EXTENSION)] ?? 'application/octet-stream';
    }
}