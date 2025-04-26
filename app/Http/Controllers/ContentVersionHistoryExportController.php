<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\ContentVersionHistory;
use Illuminate\Http\Request;
use Rap2hpoutre\FastExcel\FastExcel;

class ContentVersionHistoryExportController extends Controller
{
    public function export(Content $content, Request $request)
    {
        $query = ContentVersionHistory::with(['version', 'user'])
            ->whereHas('version', function($query) use ($content) {
                $query->where('content_id', $content->id);
            });

        // Apply same filters as history view
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $history = $query->latest()->get();

        return (new FastExcel($history))
            ->download('content-history-export.xlsx', function($record) {
                return [
                    'Version' => $record->version->version_number,
                    'Action' => ucfirst($record->action),
                    'Date' => $record->created_at->format('Y-m-d H:i:s'),
                    'User' => $record->user->name,
                    'Notes' => $record->metadata['notes'] ?? '',
                ];
            });
    }
}