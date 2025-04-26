<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VersionRestorationLog;
use Illuminate\Http\Request;

class RestorationLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = VersionRestorationLog::with(['version.content', 'user'])
            ->latest()
            ->paginate(25);

        return view('admin.restoration-logs.index', compact('logs'));
    }

    public function show(VersionRestorationLog $log)
    {
        return view('admin.restoration-logs.show', [
            'log' => $log->load(['version.content', 'user'])
        ]);
    }
}