<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NotificationFilter;
use Illuminate\Support\Facades\Auth;

class NotificationFilterController extends Controller
{
    public function getFilters(Request $request)
    {
        $user = Auth::user();
        
        return response()->json([
            'filters' => $user->notificationFilters()->get(),
            'default_filters' => NotificationFilter::defaultFilters()
        ]);
    }

    public function updateFilters(Request $request)
    {
        $validated = $request->validate([
            'filters' => 'required|array',
            'filters.*.type' => 'required|string',
            'filters.*.value' => 'required',
            'filters.*.is_active' => 'required|boolean'
        ]);

        $user = Auth::user();
        
        // Delete existing filters
        $user->notificationFilters()->delete();
        
        // Create new filters
        foreach ($validated['filters'] as $filterData) {
            $user->notificationFilters()->create([
                'type' => $filterData['type'],
                'value' => $filterData['value'],
                'is_active' => $filterData['is_active']
            ]);
        }

        return response()->json([
            'message' => 'Filters updated successfully',
            'filters' => $user->notificationFilters()->get()
        ]);
    }

    public function resetFilters(Request $request)
    {
        $user = Auth::user();
        $user->notificationFilters()->delete();

        return response()->json([
            'message' => 'Filters reset to defaults',
            'filters' => NotificationFilter::defaultFilters()
        ]);
    }
}