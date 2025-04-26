<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class NotificationSoundController extends Controller
{
    public function getSettings(Request $request)
    {
        $user = Auth::user();
        
        return response()->json([
            'sound_settings' => $user->notificationSoundSettings,
            'available_sounds' => $this->getAvailableSounds()
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'enabled' => 'required|boolean',
            'volume' => 'required|numeric|between:0,100',
            'sound' => 'required|string',
            'mute_duration' => 'nullable|numeric|min:1'
        ]);

        $user = Auth::user();
        $user->notificationSoundSettings()->updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        return response()->json([
            'message' => 'Sound settings updated',
            'settings' => $user->notificationSoundSettings
        ]);
    }

    protected function getAvailableSounds()
    {
        $soundFiles = Storage::files('public/sounds/notifications');
        
        return collect($soundFiles)->map(function($file) {
            return [
                'name' => pathinfo($file, PATHINFO_FILENAME),
                'path' => Storage::url($file)
            ];
        });
    }
}