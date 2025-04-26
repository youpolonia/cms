<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ContentVersion;

class ContentVersionApproved extends Notification implements ShouldQueue
{
    use Queueable;

    public $version;

    public function __construct(ContentVersion $version)
    {
        $this->version = $version;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Content Version Approved')
            ->line("Your version #{$this->version->version_number} of '{$this->version->content->title}' has been approved.")
            ->action('View Content', route('content.show', $this->version->content))
            ->line('Thank you for your contribution!');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => "Version #{$this->version->version_number} approved",
            'content_id' => $this->version->content_id,
            'version_id' => $this->version->id,
            'url' => route('content.versions.show', [$this->version->content, $this->version]),
        ];
    }
}