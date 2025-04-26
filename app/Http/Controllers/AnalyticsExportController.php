<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsExport;
use App\Jobs\ExportAnalyticsData;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class AnalyticsExportController extends Controller
{
    public function index()
    {
        $exports = AnalyticsExport::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('analytics.exports.index', compact('exports'));
    }

    public function create()
    {
        return view('analytics.exports.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $export = AnalyticsExport::create([
            'user_id' => auth()->id(),
            'status' => 'processing',
            'expires_at' => Carbon::now()->addDays(7)
        ]);

        ExportAnalyticsData::dispatch(
            $export,
            $validated['start_date'],
            $validated['end_date']
        );

        return response()->json([
            'message' => 'Export started',
            'export_id' => $export->id
        ], 202);
    }

    public function show(AnalyticsExport $export)
    {
        return response()->json($export);
    }

    public function destroy(AnalyticsExport $export)
    {
        $this->authorize('delete', $export);
        
        if ($export->file_path && Storage::exists($export->file_path)) {
            Storage::delete($export->file_path);
        }

        $export->delete();

        return redirect()->route('exports.index')
            ->with('success', 'Export deleted successfully');
    }

    public function download(AnalyticsExport $export)
    {
        $this->authorize('download', $export);

        if (!$export->file_path || !Storage::exists($export->file_path)) {
            abort(404);
        }

        return Storage::download($export->file_path, $export->name.'.csv');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:analytics_exports,id'
        ]);

        $exports = AnalyticsExport::whereIn('id', $request->ids)
            ->get();

        foreach ($exports as $export) {
            $this->authorize('delete', $export);
            
            if ($export->file_path && Storage::exists($export->file_path)) {
                Storage::delete($export->file_path);
            }
            $export->delete();
        }

        return response()->json([
            'message' => 'Selected exports deleted successfully',
            'count' => count($request->ids)
        ]);
    }

    public function bulkDownload(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:analytics_exports,id'
        ]);

        $exports = AnalyticsExport::whereIn('id', $request->ids)
            ->get();

        foreach ($exports as $export) {
            $this->authorize('download', $export);
            if (!$export->file_path || !Storage::exists($export->file_path)) {
                abort(404, "Export file not found: {$export->id}");
            }
        }

        $zipPath = 'exports/bulk-'.time().'.zip';
        $zip = new \ZipArchive();
        $zip->open(Storage::path($zipPath), \ZipArchive::CREATE);

        foreach ($exports as $export) {
            $zip->addFile(
                Storage::path($export->file_path),
                $export->name.'.csv'
            );
        }

        $zip->close();

        return Storage::download($zipPath, 'exports-'.now()->format('Y-m-d').'.zip')
            ->deleteFileAfterSend();
    }
}
