<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AutopilotDashboardController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');
        
        $recentTasks = \App\Models\AutopilotTask::latest()
            ->when($status !== 'all', function($query) use ($status) {
                return $query->where('status', $status);
            })
            ->limit(10)
            ->get();

        return view('autopilot.dashboard', [
            'recentTasks' => $recentTasks,
            'filterStatus' => $status
        ]);
    }
}