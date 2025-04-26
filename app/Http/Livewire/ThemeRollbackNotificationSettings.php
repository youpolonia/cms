<?php

namespace App\Http\Livewire;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ThemeRollbackNotificationSettings extends Component
{
    public bool $enabled;
    public array $selectedUsers = [];
    public array $selectedRoles = [];

    protected $listeners = ['settingsSaved' => '$refresh'];

    public function mount()
    {
        $this->enabled = Auth::user()->notificationSettings()
            ->where('key', 'theme_rollback_notifications_enabled')
            ->first()
            ?->value ?? false;

        $this->selectedUsers = Auth::user()->notificationSettings()
            ->where('key', 'theme_rollback_notifications_users')
            ->first()
            ?->value ?? [];

        $this->selectedRoles = Auth::user()->notificationSettings()
            ->where('key', 'theme_rollback_notifications_roles')
            ->first()
            ?->value ?? [];
    }

    public function getUsersProperty()
    {
        return User::query()
            ->where('id', '!=', Auth::id())
            ->orderBy('name')
            ->get();
    }

    public function getRolesProperty()
    {
        return Role::query()
            ->orderBy('name')
            ->get();
    }

    public function save()
    {
        Auth::user()->notificationSettings()->updateOrCreate(
            ['key' => 'theme_rollback_notifications_enabled'],
            ['value' => $this->enabled]
        );

        Auth::user()->notificationSettings()->updateOrCreate(
            ['key' => 'theme_rollback_notifications_users'],
            ['value' => $this->selectedUsers]
        );

        Auth::user()->notificationSettings()->updateOrCreate(
            ['key' => 'theme_rollback_notifications_roles'],
            ['value' => $this->selectedRoles]
        );

        $this->emit('settingsSaved');
        $this->dispatchBrowserEvent('notify', 'Settings saved successfully!');
    }

    public function render()
    {
        return view('livewire.theme-rollback-notification-settings');
    }
}
