<?php

namespace App\Services;

use App\Models\SchedulingNotification;
use App\Models\SchedulingNotificationPreference;
use App\Models\User;
use App\Models\ContentSchedule;
use Illuminate\Support\Facades\Mail;
use App\Mail\SchedulingNotificationMail;

class SchedulingNotificationService
{
    public function notifyUpcoming(ContentSchedule $schedule, int $minutesBefore = 30)
    {
        $users = $this->getNotificationRecipients($schedule);
        
        foreach ($users as $user) {
            $preferences = $this->getUserPreferences($user);
            
            if ($preferences->in_app_upcoming) {
                $this->createInAppNotification(
                    $user,
                    $schedule,
                    'upcoming',
                    "Content '{$schedule->content->title}' is scheduled to publish in {$minutesBefore} minutes"
                );
            }

            if ($preferences->email_upcoming) {
                Mail::to($user->email)->send(
                    new SchedulingNotificationMail(
                        $user,
                        $schedule,
                        'upcoming',
                        "Content Publishing Soon",
                        "Content '{$schedule->content->title}' is scheduled to publish in {$minutesBefore} minutes"
                    )
                );
            }
        }
    }

    public function notifyConflict(ContentSchedule $schedule, array $conflicts)
    {
        $users = $this->getNotificationRecipients($schedule);
        $conflictDetails = collect($conflicts)->map(fn($c) => $c['message'])->implode("\n");

        foreach ($users as $user) {
            $preferences = $this->getUserPreferences($user);
            
            if ($preferences->in_app_conflicts) {
                $this->createInAppNotification(
                    $user,
                    $schedule,
                    'conflict',
                    "Schedule conflict detected for '{$schedule->content->title}':\n{$conflictDetails}",
                    ['conflicts' => $conflicts]
                );
            }

            if ($preferences->email_conflicts) {
                Mail::to($user->email)->send(
                    new SchedulingNotificationMail(
                        $user,
                        $schedule,
                        'conflict',
                        "Schedule Conflict Detected",
                        "Schedule conflict detected for '{$schedule->content->title}':\n{$conflictDetails}",
                        ['conflicts' => $conflicts]
                    )
                );
            }
        }
    }

    public function notifyCompleted(ContentSchedule $schedule)
    {
        $users = $this->getNotificationRecipients($schedule);
        
        foreach ($users as $user) {
            $preferences = $this->getUserPreferences($user);
            
            if ($preferences->in_app_completed) {
                $this->createInAppNotification(
                    $user,
                    $schedule,
                    'completed',
                    "Content '{$schedule->content->title}' has been successfully published"
                );
            }

            if ($preferences->email_completed) {
                Mail::to($user->email)->send(
                    new SchedulingNotificationMail(
                        $user,
                        $schedule,
                        'completed',
                        "Content Published Successfully",
                        "Content '{$schedule->content->title}' has been successfully published"
                    )
                );
            }
        }
    }

    public function notifyChanged(ContentSchedule $schedule, array $changes)
    {
        $users = $this->getNotificationRecipients($schedule);
        $changeDetails = collect($changes)->map(fn($c) => "{$c['field']} changed from '{$c['old']}' to '{$c['new']}'")->implode("\n");

        foreach ($users as $user) {
            $preferences = $this->getUserPreferences($user);
            
            if ($preferences->in_app_changes) {
                $this->createInAppNotification(
                    $user,
                    $schedule,
                    'changed',
                    "Schedule for '{$schedule->content->title}' was modified:\n{$changeDetails}",
                    ['changes' => $changes]
                );
            }

            if ($preferences->email_changes) {
                Mail::to($user->email)->send(
                    new SchedulingNotificationMail(
                        $user,
                        $schedule,
                        'changed',
                        "Schedule Modified",
                        "Schedule for '{$schedule->content->title}' was modified:\n{$changeDetails}",
                        ['changes' => $changes]
                    )
                );
            }
        }
    }

    protected function getNotificationRecipients(ContentSchedule $schedule)
    {
        // Get content owners, schedule creator, and anyone with view permissions
        return User::whereHas('roles', function($query) use ($schedule) {
                $query->whereIn('name', ['admin', 'editor', 'content-manager'])
                      ->orWhere('id', $schedule->created_by);
            })
            ->orWhere('id', $schedule->content->created_by)
            ->get();
    }

    protected function getUserPreferences(User $user)
    {
        return SchedulingNotificationPreference::firstOrCreate(
            ['user_id' => $user->id],
            $this->getDefaultPreferences()
        );
    }

    protected function getDefaultPreferences()
    {
        return [
            'email_upcoming' => true,
            'email_conflicts' => true,
            'email_completed' => false,
            'email_changes' => true,
            'in_app_upcoming' => true,
            'in_app_conflicts' => true,
            'in_app_completed' => true,
            'in_app_changes' => true
        ];
    }

    protected function createInAppNotification(
        User $user,
        ContentSchedule $schedule,
        string $type,
        string $message,
        array $metadata = null
    ) {
        SchedulingNotification::create([
            'user_id' => $user->id,
            'schedule_id' => $schedule->id,
            'type' => $type,
            'message' => $message,
            'metadata' => $metadata,
            'read_at' => null
        ]);
    }
}