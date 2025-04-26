<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\User;

class NotificationSettings extends Component
{
    public User $user;
    public $preferences = [];
    public $error = null;

    public function mount(User $user)
    {
        $this->user = $user;
        $this->preferences = [
            'content_approval' => $user->getNotificationPreference('content_approval', true),
            'content_rejection' => $user->getNotificationPreference('content_rejection', true),
            'content_published' => $user->getNotificationPreference('content_published', true),
            'theme_installed' => $user->getNotificationPreference('theme_installed', true)
        ];
    }

    public function updateNotificationPreferences($preferences)
    {
        try {
            foreach ($preferences as $type => $value) {
                $this->user->setNotificationPreference($type, $value);
            }
            $this->user->save();
            $this->dispatch('notify', type: 'success', message: 'Preferences saved');
        } catch (\Exception $e) {
            $this->error = 'Failed to save preferences. Please try again.';
            throw $e;
        }
    }

    public function render()
    {
        return view('components.notification-settings');
    }
}
