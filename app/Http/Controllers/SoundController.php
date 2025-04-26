<?php

namespace App\Http\Controllers;

use App\Models\NotificationSound;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SoundController extends Controller
{
    public function index()
    {
        return view('sounds.index', [
            'sounds' => NotificationSound::all()
        ]);
    }

    public function play($id)
    {
        $sound = NotificationSound::findOrFail($id);
        $filePath = storage_path('app/public/sounds/'.$sound->file_path);
        
        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->file($filePath);
    }

    public function getDefaultSound()
    {
        $sound = NotificationSound::where('is_default', true)->first();
        
        if (!$sound) {
            $sound = NotificationSound::first();
        }

        if (!$sound) {
            abort(404);
        }

        $filePath = storage_path('app/public/sounds/'.$sound->file_path);
        return response()->file($filePath);
    }
}