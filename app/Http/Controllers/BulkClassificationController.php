<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessBulkClassification;
use App\Models\ErrorClassification;
use App\Models\ExportHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;

class BulkClassificationController extends Controller
{
    public function index()
    {
        return view('bulk-classification.index', [
            'pendingJobs' => ErrorClassification::where('batch_id', '!=', null)
                ->where('completed_at', null)
                ->count()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'export_ids' => 'required|array',
            'export_ids.*' => 'exists:export_histories,id'
        ]);

        $batch = Bus::batch([])->dispatch();

        foreach ($request->export_ids as $exportId) {
            $batch->add(new ProcessBulkClassification($exportId));
        }

        return redirect()->route('bulk-classification.index')
            ->with('status', 'Bulk classification started for '.count($request->export_ids).' exports');
    }

    public function show($batchId)
    {
        return view('bulk-classification.show', [
            'batch' => Bus::findBatch($batchId)
        ]);
    }
}