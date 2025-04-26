<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\NotificationPreference;
use App\Models\NotificationSound;

class NotificationPreferences extends Component
{
    public $preferences = [
        'content_published' => [
            'enabled' => true,
            'sound_id' => null,
            'volume' => 80
        ],
        'content_updated' => [
            'enabled' => true,
            'sound_id' => null,
            'volume' => 80
        ],
        'approval_required' => [
            'enabled' => true,
            'sound_id' => null,
            'volume' => 80
        ],
        'approval_completed' => [
            'enabled' => true,
            'sound_id' => null,
            'volume' => 80
        ]
    ];

    public $sounds = [];

    public function mount()
    {
        $this->sounds = NotificationSound::all();
        
        // Load existing preferences
        $userPreferences = NotificationPreference::where('user_id', auth()->id())->get();
        
        foreach ($userPreferences as $pref) {
            if (isset($this->preferences[$pref->type])) {
                $this->preferences[$pref->type] = [
                    'enabled' => $pref->enabled,
                    'sound_id' => $pref->sound_id,
                    'volume' => $pref->volume
                ];
            }
        }
    }

    public function savePreferences()
    {
        $this->validate([
            'preferences.*.enabled' => 'boolean',
            'preferences.*.sound_id' => 'nullable|exists:notification_sounds,id',
            'preferences.*.volume' => 'numeric|min:0|max:100'
        ]);

        foreach ($this->preferences as $type => $settings) {
            NotificationPreference::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'type' => $type
                ],
                [
                    'enabled' => $settings['enabled'],
                    'sound_id' => $settings['sound_id'],
                    'volume' => $settings['volume']
                ]
            );
        }

        session()->flash('message', 'Preferences saved successfully!');
    }

    public function render()
    {
        return view('livewire.notification-preferences');
    }
}