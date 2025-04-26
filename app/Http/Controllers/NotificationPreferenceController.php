<?php

namespace App\Http\Controllers;

use App\Models\NotificationPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationPreferenceController extends Controller
{
    public function index()
    {
        $preferences = Auth::user()
            ->notificationPreferences()
            ->get()
            ->keyBy('notification_type');

        $defaults = NotificationPreference::getDefaults();

        return response()->json([
            'preferences' => $preferences,
            'defaults' => $defaults
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'notification_type' => 'required|string',
            'email_enabled' => 'boolean',
            'push_enabled' => 'boolean',
            'in_app_enabled' => 'boolean',
            'channels' => 'nullable|array'
        ]);

        $preference = Auth::user()
            ->notificationPreferences()
            ->updateOrCreate(
                ['notification_type' => $validated['notification_type']],
                $validated
            );

        return response()->json($preference);
    }

    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'preferences' => 'required|array',
            'preferences.*.notification_type' => 'required|string',
            'preferences.*.email_enabled' => 'boolean',
            'preferences.*.push_enabled' => 'boolean',
            'preferences.*.in_app_enabled' => 'boolean',
            'preferences.*.channels' => 'nullable|array'
        ]);

        foreach ($validated['preferences'] as $pref) {
            Auth::user()
                ->notificationPreferences()
                ->updateOrCreate(
                    ['notification_type' => $pref['notification_type']],
                    $pref
                );
        }

        return response()->json(['success' => true]);
    }
}