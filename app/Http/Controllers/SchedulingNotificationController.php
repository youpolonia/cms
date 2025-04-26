<?php

namespace App\Http\Controllers;

use App\Models\SchedulingNotification;
use App\Models\SchedulingNotificationPreference;
use Illuminate\Http\Request;
use App\Services\SchedulingNotificationService;

class SchedulingNotificationController extends Controller
{
    protected $notificationService;

    public function __construct(SchedulingNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $notifications = $request->user()
            ->schedulingNotifications()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('scheduling.notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $request->user()->schedulingNotifications()->whereNull('read_at')->count()
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()
            ->schedulingNotifications()
            ->findOrFail($id);

        $notification->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()
            ->schedulingNotifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function preferences(Request $request)
    {
        $preferences = $request->user()->schedulingNotificationPreference ?? 
            $this->notificationService->getUserPreferences($request->user());

        return view('scheduling.notifications.preferences', [
            'preferences' => $preferences
        ]);
    }

    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'email_upcoming' => 'boolean',
            'email_conflicts' => 'boolean',
            'email_completed' => 'boolean',
            'email_changes' => 'boolean',
            'in_app_upcoming' => 'boolean',
            'in_app_conflicts' => 'boolean',
            'in_app_completed' => 'boolean',
            'in_app_changes' => 'boolean'
        ]);

        $preferences = SchedulingNotificationPreference::updateOrCreate(
            ['user_id' => $request->user()->id],
            $validated
        );

        return redirect()->back()
            ->with('success', 'Notification preferences updated successfully');
    }

    public function unreadCount(Request $request)
    {
        return response()->json([
            'count' => $request->user()
                ->schedulingNotifications()
                ->whereNull('read_at')
                ->count()
        ]);
    }
}