<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function edit(User $user)
    {
        $roles = Role::all();
        return view('profile.edit', [
            'user' => $user,
            'roles' => $roles,
            'currentRole' => $user->roles->first()?->name
        ]);
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|exists:roles,name'
        ]);

        $user->syncRoles($request->role);

        return redirect()->back()
            ->with('success', 'User role updated successfully');
    }

    public function updateNotificationPreferences(Request $request, User $user)
    {
        $validated = $request->validate([
            'preferences' => 'required|array',
            'preferences.*' => 'boolean'
        ]);

        $user->notification_preferences = array_merge(
            $user->notification_preferences ?? [],
            $validated['preferences']
        );
        $user->save();

        return redirect()->back()
            ->with('success', 'Notification preferences updated');
    }
}
