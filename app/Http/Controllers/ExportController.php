<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsExport;
use App\Jobs\ExportAnalyticsData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExportController extends Controller
{
    public function index()
    {
        return view('exports.index', [
            'exports' => Auth::user()->exports()->latest()->paginate(10)
        ]);
    }

    public function create()
    {
        return view('exports.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'required|in:csv,xlsx,json',
            'filters.content_type' => 'sometimes|string',
            'filters.user_activity' => 'sometimes|boolean',
            'expires_at' => 'nullable|date'
        ]);

        $export = AnalyticsExport::create([
            'user_id' => Auth::id(),
            'status' => 'pending',
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'format' => $validated['format'],
            'filters' => $validated['filters'] ?? [],
            'expires_at' => $validated['expires_at'] ?? null
        ]);

        ExportAnalyticsData::dispatch($export);

        return redirect()->route('exports.index')
            ->with('success', 'Export started successfully. You will be notified when it completes.');
    }

    public function download(AnalyticsExport $export)
    {
        abort_if($export->user_id !== Auth::id(), 403);
        abort_if($export->status !== 'completed', 404);

        return response()->download(storage_path('app/' . $export->file_path));
    }

    public function destroy(AnalyticsExport $export)
    {
        abort_if($export->user_id !== Auth::id(), 403);

        if ($export->file_path) {
            \Storage::delete($export->file_path);
        }

        $export->delete();

        return redirect()->route('exports.index')
            ->with('success', 'Export deleted successfully.');
    }
}