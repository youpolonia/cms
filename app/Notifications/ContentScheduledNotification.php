<?php

namespace App\Notifications;

use App\Models\Content;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContentScheduledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Content $content
    ) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Content Scheduled: ' . $this->content->title)
            ->line('The content "' . $this->content->title . '" is scheduled to publish soon.')
            ->line('Scheduled for: ' . $this->content->scheduled_at->format('Y-m-d H:i'))
            ->action('Review Content', route('contents.show', $this->content));
    }

    public function toArray($notifiable)
    {
        return [
            'content_id' => $this->content->id,
            'title' => $this->content->title,
            'scheduled_at' => $this->content->scheduled_at,
            'message' => 'Content scheduled for publication',
            'url' => route('contents.show', $this->content)
        ];
    }
}