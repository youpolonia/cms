<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnalyticsExport;
use App\Jobs\ExportAnalyticsData;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Notifications\AnalyticsExportReady;
use App\Notifications\AnalyticsExportDeleted;

class NotificationExportController extends Controller
{
    public function export(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'format' => 'required|in:csv,json,xlsx',
            'date_range' => 'required|array',
            'date_range.start' => 'required|date',
            'date_range.end' => 'required|date',
            'filters' => 'nullable|array'
        ]);

        // Create export record
        $export = AnalyticsExport::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'format' => $validated['format'],
            'filters' => $validated['filters'] ?? [],
            'start_date' => $validated['date_range']['start'],
            'end_date' => $validated['date_range']['end']
        ]);

        // Dispatch export job
        ExportAnalyticsData::dispatch($export, $user);

        return response()->json([
            'message' => 'Export started successfully',
            'export_id' => $export->id
        ]);
    }

    public function download($id)
    {
        $export = Auth::user()->analyticsExports()->findOrFail($id);

        if ($export->status !== 'completed') {
            return response()->json(['error' => 'Export not ready'], 400);
        }

        return Storage::download($export->file_path, $export->file_name);
    }

    public function history(Request $request)
    {
        $exports = Auth::user()->analyticsExports()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($exports);
    }

    public function delete($id)
    {
        $export = Auth::user()->analyticsExports()->findOrFail($id);

        // Delete file if exists
        if ($export->file_path && Storage::exists($export->file_path)) {
            Storage::delete($export->file_path);
        }

        $export->delete();

        // Notify user
        Auth::user()->notify(new AnalyticsExportDeleted($export));

        return response()->json([
            'message' => 'Export deleted successfully'
        ]);
    }
}