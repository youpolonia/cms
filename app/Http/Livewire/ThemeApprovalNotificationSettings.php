<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ThemeApprovalNotificationSettings extends Component
{
    public bool $enabled;
    public bool $includeComments;
    public bool $includeNextSteps;

    protected $rules = [
        'enabled' => 'boolean',
        'includeComments' => 'boolean',
        'includeNextSteps' => 'boolean'
    ];

    public function mount()
    {
        $prefs = Auth::user()->notification_preferences['theme_approval'] ?? [
            'enabled' => true,
            'includeComments' => true,
            'includeNextSteps' => true
        ];

        $this->enabled = $prefs['enabled'];
        $this->includeComments = $prefs['includeComments'];
        $this->includeNextSteps = $prefs['includeNextSteps'];
    }

    public function updated()
    {
        $this->validate();

        $user = Auth::user();
        $prefs = $user->notification_preferences ?? [];
        $prefs['theme_approval'] = [
            'enabled' => $this->enabled,
            'includeComments' => $this->includeComments,
            'includeNextSteps' => $this->includeNextSteps
        ];

        $user->notification_preferences = $prefs;
        $user->save();

        $this->emit('saved');
    }

    public function render()
    {
        return view('livewire.theme-approval-notification-settings');
    }
}
