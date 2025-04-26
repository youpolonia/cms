<?php

namespace App\Http\Livewire;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ThemeUpdateNotificationSettings extends Component
{
    public User $user;
    public bool $enabled;
    public bool $email;

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->enabled = $this->user->notification_preferences['theme_updates']['enabled'] ?? false;
        $this->email = $this->user->notification_preferences['theme_updates']['email'] ?? false;
    }

    public function render(): View
    {
        return view('livewire.theme-update-notification-settings');
    }

    public function updatedEnabled($value): void
    {
        $this->savePreferences();
    }

    public function updatedEmail($value): void
    {
        $this->savePreferences();
    }

    protected function savePreferences(): void
    {
        $preferences = $this->user->notification_preferences;
        $preferences['theme_updates'] = [
            'enabled' => $this->enabled,
            'email' => $this->email
        ];

        $this->user->update([
            'notification_preferences' => $preferences
        ]);

        $this->dispatch('saved');
    }
}
