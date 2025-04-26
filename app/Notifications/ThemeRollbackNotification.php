<?php

namespace App\Notifications;

use App\Models\Theme;
use App\Models\ThemeVersionRollback;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class ThemeRollbackNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ThemeVersionRollback $rollback,
        public string $status = 'initiated' // initiated|completed|failed
    ) {}

    public function via($notifiable)
    {
        $channels = ['database'];
        
        if ($notifiable->notification_preferences['theme_rollbacks']['email'] ?? true) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail($notifiable)
    {
        $subject = match($this->status) {
            'initiated' => "Theme Rollback Initiated: {$this->rollback->theme->name}",
            'completed' => "Theme Rollback Completed: {$this->rollback->theme->name}",
            'failed' => "Theme Rollback Failed: {$this->rollback->theme->name}",
            default => "Theme Rollback Notification"
        };

        return (new MailMessage)
            ->subject($subject)
            ->markdown('notifications.theme-rollback', [
                'rollback' => $this->rollback,
                'status' => $this->status,
                'notifiable' => $notifiable
            ]);
    }

    public function toDatabase($notifiable)
    {
        $message = match($this->status) {
            'initiated' => sprintf(
                "Rollback initiated from %s to %s%s",
                $this->rollback->currentVersion->version,
                $this->rollback->rollbackVersion->version,
                $this->rollback->branch_name ? " (Branch: {$this->rollback->branch_name})" : ""
            ),
            'completed' => sprintf(
                "Successfully rolled back to version %s%s",
                $this->rollback->rollbackVersion->version,
                $this->rollback->rollback_branch ? " (Branch: {$this->rollback->rollback_branch})" : ""
            ),
            'failed' => "Rollback failed: {$this->rollback->error_message}",
            default => "Theme rollback notification"
        };

        return new DatabaseMessage([
            'theme_id' => $this->rollback->theme_id,
            'rollback_id' => $this->rollback->id,
            'title' => "Theme Rollback: {$this->rollback->theme->name}",
            'message' => $message,
            'action_url' => route('themes.versions.rollback.details', [
                'theme' => $this->rollback->theme_id,
                'rollback' => $this->rollback->id
            ]),
            'icon' => 'rollback',
            'status' => $this->status
        ]);
    }

    public function toArray($notifiable)
    {
        return [
            'theme_id' => $this->rollback->theme_id,
            'rollback_id' => $this->rollback->id,
            'status' => $this->status,
            'branch_name' => $this->rollback->branch_name,
            'rollback_branch' => $this->rollback->rollback_branch,
            'message' => $this->toDatabase($notifiable)->message
        ];
    }
}
