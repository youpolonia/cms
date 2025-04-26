<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::with(['user', 'auditable'])
            ->latest()
            ->paginate(25);

        return view('audit.index', compact('logs'));
    }

    public function show(AuditLog $auditLog)
    {
        return view('audit.show', compact('auditLog'));
    }
}