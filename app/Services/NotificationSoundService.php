<?php

namespace App\Services;

use App\Models\NotificationSound;
use Illuminate\Support\Facades\Storage;

class NotificationSoundService
{
    public function getAllSounds()
    {
        return NotificationSound::orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();
    }

    public function setDefaultSound(int $soundId)
    {
        // Clear current default
        NotificationSound::where('is_default', true)->update(['is_default' => false]);
        
        // Set new default
        $sound = NotificationSound::findOrFail($soundId);
        $sound->update(['is_default' => true]);

        return $sound;
    }

    public function playSound(NotificationSound $sound)
    {
        if (Storage::exists($sound->file_path)) {
            // In a real implementation, this would trigger audio playback
            // For now we'll just return the file path
            return Storage::url($sound->file_path);
        }

        return null;
    }
}