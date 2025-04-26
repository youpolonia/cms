<?php

namespace App\Http\Controllers;

use App\Models\ContentVersion;
use App\Jobs\ProcessVersionRestoration;
use Illuminate\Http\Request;

class ContentVersionRestoreController extends Controller
{
    public function restore(Request $request)
    {
        $request->validate([
            'version_id' => 'required|exists:content_versions,id'
        ]);

        ProcessVersionRestoration::dispatch(
            $request->version_id,
            auth()->id()
        );

        return response()->json([
            'message' => 'Version restoration started'
        ]);
    }
}