<?php

namespace App\Notifications;

use App\Models\ModerationQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ModerationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ModerationQueue $moderation,
        public string $action
    ) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Content {$this->action}")
            ->line("Your content has been {$this->action} by the moderation team.")
            ->lineIf(
                $this->action === 'rejected',
                "Reason: {$this->moderation->rejection_reason}"
            )
            ->action('View Content', route('content.show', $this->moderation->content_id));
    }

    public function toArray($notifiable)
    {
        return [
            'content_id' => $this->moderation->content_id,
            'action' => $this->action,
            'reason' => $this->moderation->rejection_reason ?? null,
            'moderator_id' => $this->moderation->moderator_id,
        ];
    }
}