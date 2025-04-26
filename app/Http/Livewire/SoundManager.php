<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\NotificationSound;
use Illuminate\Support\Facades\Storage;

class SoundManager extends Component
{
    use WithFileUploads;

    public $sounds = [];
    public $newSound;
    public $name;
    public $description;
    public $category = 'notification';
    public $isDefault = false;
    public $isActive = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'category' => 'required|string|in:notification,alert,feedback',
        'newSound' => 'required|file|mimes:mp3,wav|max:2048',
        'isDefault' => 'boolean',
        'isActive' => 'boolean'
    ];

    public function mount()
    {
        $this->sounds = NotificationSound::all();
    }

    public function saveSound()
    {
        $this->validate();

        $path = $this->newSound->store('public/sounds');
        $filename = basename($path);

        NotificationSound::create([
            'name' => $this->name,
            'description' => $this->description,
            'file_path' => $filename,
            'duration' => 0, // Will be calculated after upload
            'category' => $this->category,
            'is_default' => $this->isDefault,
            'is_active' => $this->isActive
        ]);

        $this->reset(['name', 'description', 'newSound', 'isDefault']);
        $this->sounds = NotificationSound::all();
        $this->emit('soundAdded');
    }

    public function deleteSound($id)
    {
        $sound = NotificationSound::findOrFail($id);
        Storage::delete('public/sounds/'.$sound->file_path);
        $sound->delete();
        $this->sounds = NotificationSound::all();
    }

    public function setDefault($id)
    {
        NotificationSound::where('is_default', true)->update(['is_default' => false]);
        $sound = NotificationSound::findOrFail($id);
        $sound->update(['is_default' => true]);
        $this->sounds = NotificationSound::all();
    }

    public function toggleActive($id)
    {
        $sound = NotificationSound::findOrFail($id);
        $sound->update(['is_active' => !$sound->is_active]);
        $this->sounds = NotificationSound::all();
    }

    public function render()
    {
        return view('livewire.sound-manager');
    }
}