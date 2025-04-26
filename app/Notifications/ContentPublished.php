<?php

namespace App\Notifications;

use App\Models\Content;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContentPublished extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Content $content
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Your content has been published")
            ->line("Your content '{$this->content->title}' has been published as scheduled.")
            ->action('View Content', route('contents.show', $this->content))
            ->line('Thank you for using our platform!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'content_id' => $this->content->id,
            'title' => $this->content->title,
            'message' => "Your content '{$this->content->title}' has been published",
            'url' => route('contents.show', $this->content)
        ];
    }
}
