<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ContentVersion;

class ContentVersionRejected extends Notification implements ShouldQueue
{
    use Queueable;

    public $version;
    public $reason;

    public function __construct(ContentVersion $version, string $reason = null)
    {
        $this->version = $version;
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->subject('Content Version Rejected')
            ->line("Your version #{$this->version->version_number} of '{$this->version->content->title}' has been rejected.");

        if ($this->reason) {
            $message->line("Reason: {$this->reason}");
        }

        return $message->action('View Content', route('content.show', $this->version->content))
                      ->line('Please review the guidelines and try again.');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => "Version #{$this->version->version_number} rejected",
            'content_id' => $this->version->content_id,
            'version_id' => $this->version->id,
            'reason' => $this->reason,
            'url' => route('content.versions.show', [$this->version->content, $this->version]),
        ];
    }
}