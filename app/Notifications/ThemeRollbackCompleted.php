<?php

namespace App\Notifications;

use App\Models\ThemeVersionRollback;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ThemeRollbackCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ThemeVersionRollback $rollback
    ) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Theme Rollback Completed: {$this->rollback->version->theme->name}")
            ->markdown('emails.theme-rollback-completed', [
                'rollback' => $this->rollback,
                'url' => route('themes.versions.rollback.details', [
                    $this->rollback->version->theme,
                    $this->rollback
                ])
            ]);
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'theme_rollback_completed',
            'theme_id' => $this->rollback->version->theme->id,
            'theme_name' => $this->rollback->version->theme->name,
            'from_version' => $this->rollback->version->id,
            'to_version' => $this->rollback->rollbackToVersion->id,
            'status' => $this->rollback->status,
            'completed_at' => $this->rollback->completed_at,
            'url' => route('themes.versions.rollback.details', [
                $this->rollback->version->theme,
                $this->rollback
            ])
        ];
    }
}
