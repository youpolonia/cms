<?php

namespace App\Notifications;

use App\Models\ModerationQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContentModerated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ModerationQueue $moderation
    ) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Content Moderation Update')
            ->line("Your content '{$this->moderation->content->title}' has been moderated")
            ->line("Status: {$this->moderation->status}")
            ->line("Action: {$this->moderation->action}")
            ->line("Reason: {$this->moderation->reason}")
            ->action('View Content', route('content.show', $this->moderation->content_id));
    }

    public function toArray($notifiable)
    {
        return [
            'content_id' => $this->moderation->content_id,
            'moderation_id' => $this->moderation->id,
            'status' => $this->moderation->status,
            'action' => $this->moderation->action,
            'reason' => $this->moderation->reason,
            'url' => route('content.show', $this->moderation->content_id),
        ];
    }
}