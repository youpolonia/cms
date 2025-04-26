<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\NotificationSound;
use Illuminate\Support\Facades\Storage;

class SoundPreview extends Component
{
    public $selectedSound = null;
    public $isPlaying = false;
    public $volume = 50;
    public $playbackRate = 1.0;
    public $availableSounds = [];

    protected $listeners = ['stopAllSounds' => 'stopSound'];

    public function mount()
    {
        $this->availableSounds = NotificationSound::all();
    }

    public function playSound($soundId)
    {
        $this->emit('stopAllSounds');
        $this->selectedSound = $soundId;
        $this->isPlaying = true;
    }

    public function stopSound()
    {
        $this->isPlaying = false;
    }

    public function updatedVolume($value)
    {
        $this->emit('updateVolume', $value);
    }

    public function updatedPlaybackRate($value)
    {
        $this->emit('updatePlaybackRate', $value);
    }

    public function render()
    {
        return view('livewire.sound-preview', [
            'sounds' => $this->availableSounds
        ]);
    }
}