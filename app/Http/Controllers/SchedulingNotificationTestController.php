<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\SchedulingNotificationMail;
use App\Models\ContentSchedule;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class SchedulingNotificationTestController extends Controller
{
    public function create()
    {
        return view('scheduling.notifications.test');
    }

    public function sendTest(Request $request)
    {
        $request->validate([
            'type' => 'required|in:upcoming,conflict,completed,changed',
            'channel' => 'required|in:email,in_app,both'
        ]);

        $user = Auth::user();
        $schedule = ContentSchedule::first() ?? new ContentSchedule([
            'content_id' => 1,
            'publish_at' => now()->addDay(),
            'published_at' => now()
        ]);

        $metadata = [
            'conflicting_content' => (object)['title' => 'Test Conflict Content'],
            'conflict_type' => 'time',
            'initial_views' => 42
        ];

        $messages = [
            'upcoming' => 'Test upcoming schedule notification',
            'conflict' => 'Test schedule conflict notification',
            'completed' => 'Test publication completed notification', 
            'changed' => 'Test schedule changed notification'
        ];

        if (in_array($request->channel, ['email', 'both'])) {
            Mail::to($user)->send(new SchedulingNotificationMail(
                $user,
                $schedule,
                $request->type,
                'Test Notification: ' . ucfirst($request->type),
                $messages[$request->type],
                $metadata
            ));
        }

        if (in_array($request->channel, ['in_app', 'both'])) {
            $user->notify(new \App\Notifications\SchedulingNotification(
                $request->type,
                $messages[$request->type],
                $schedule,
                $metadata
            ));
        }

        return back()->with('success', 'Test notification sent successfully');
    }
}