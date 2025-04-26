<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ModerationDecision extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $decision,
        public string $contentTitle,
        public ?string $reason = null
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject("Content Moderation Decision: {$this->contentTitle}")
            ->line("Your content '{$this->contentTitle}' has been {$this->decision}.");

        if ($this->reason) {
            $message->line("Reason: {$this->reason}");
        }

        return $message->action('View Content', url('/content'));
    }

    public function toArray($notifiable): array
    {
        return [
            'decision' => $this->decision,
            'content_title' => $this->contentTitle,
            'reason' => $this->reason
        ];
    }
}
