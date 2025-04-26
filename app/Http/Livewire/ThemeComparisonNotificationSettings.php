<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\User;

class ThemeComparisonNotificationSettings extends Component
{
    public $user;
    public $preferences = [
        'theme_comparisons' => true,
        'theme_comparison_frequency' => 'immediate',
        'theme_comparison_threshold' => 0
    ];

    protected $rules = [
        'preferences.theme_comparisons' => 'boolean',
        'preferences.theme_comparison_frequency' => 'in:immediate,daily,weekly',
        'preferences.theme_comparison_threshold' => 'numeric|min:0|max:100'
    ];

    public function mount(User $user)
    {
        $this->user = $user;
        $this->preferences['theme_comparisons'] = $user->getNotificationPreference('theme_comparisons', true);
        $this->preferences['theme_comparison_frequency'] = $user->getNotificationPreference('theme_comparison_frequency', 'immediate');
        $this->preferences['theme_comparison_threshold'] = $user->getNotificationPreference('theme_comparison_threshold', 0);
    }

    public function updated()
    {
        $this->validate();
        $this->updateNotificationPreferences();
    }

    public function updateNotificationPreferences()
    {
        $this->user->updateNotificationPreferences($this->preferences);
        $this->emit('notify', ['type' => 'success', 'message' => 'Preferences saved']);
    }

    public function render()
    {
        return view('components.comparison-notification-settings-form');
    }
}
