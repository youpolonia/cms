<?php

namespace App\Notifications;

use App\Models\Content;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContentExpired extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Content $content)
    {
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your content has expired')
            ->line("Your content '{$this->content->title}' has expired as scheduled.")
            ->action('View Content', route('contents.show', $this->content))
            ->line('You can create a new version if needed.');
    }

    public function toArray($notifiable)
    {
        return [
            'content_id' => $this->content->id,
            'title' => $this->content->title,
            'message' => "Your content '{$this->content->title}' has expired",
            'url' => route('contents.show', $this->content),
        ];
    }
}
