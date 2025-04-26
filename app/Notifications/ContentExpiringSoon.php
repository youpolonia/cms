<?php

namespace App\Notifications;

use App\Models\Content;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ContentExpiringSoon extends Notification implements ShouldQueue
{
    use Queueable;

    public $content;
    public $daysRemaining;

    public function __construct(Content $content, $daysRemaining)
    {
        $this->content = $content;
        $this->daysRemaining = $daysRemaining;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Content Expiring Soon: {$this->content->title}")
            ->line("Your content '{$this->content->title}' will expire in {$this->daysRemaining} days.")
            ->action('View Content', route('contents.show', $this->content))
            ->line('Thank you for using our CMS!');
    }

    public function toArray($notifiable)
    {
        return [
            'content_id' => $this->content->id,
            'title' => $this->content->title,
            'message' => "Content will expire in {$this->daysRemaining} days",
            'link' => route('contents.show', $this->content)
        ];
    }
}