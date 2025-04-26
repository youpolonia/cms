<?php

namespace App\Http\Controllers;

use App\Models\ContentVersion;
use Illuminate\Http\Request;
use App\Jobs\ProcessBulkVersionExport;
use App\Jobs\ProcessBulkVersionDeletion;

class ContentVersionBulkController extends Controller
{
    public function delete(Request $request)
    {
        $request->validate([
            'versions' => 'required|array',
            'versions.*' => 'exists:content_versions,id'
        ]);

        ProcessBulkVersionDeletion::dispatch(
            $request->versions,
            auth()->id()
        );

        return response()->json([
            'message' => 'Bulk deletion started'
        ]);
    }

    public function export(Request $request)
    {
        $request->validate([
            'versions' => 'required|array',
            'versions.*' => 'exists:content_versions,id',
            'format' => 'required|in:csv,json,pdf'
        ]);

        ProcessBulkVersionExport::dispatch(
            $request->versions,
            $request->format,
            auth()->id()
        );

        return response()->json([
            'message' => 'Bulk export started'
        ]);
    }

    public function changeStatus(Request $request)
    {
        $request->validate([
            'versions' => 'required|array',
            'versions.*' => 'exists:content_versions,id',
            'status' => 'required|in:draft,published,archived'
        ]);

        ContentVersion::whereIn('id', $request->versions)
            ->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Status updated for selected versions'
        ]);
    }
}