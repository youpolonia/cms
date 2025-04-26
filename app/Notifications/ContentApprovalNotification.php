<?php

namespace App\Notifications;

use App\Models\ModerationQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContentApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ModerationQueue $moderationQueue
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $action = match($this->moderationQueue->status) {
            'pending' => 'requires your approval',
            'approved' => 'has been approved',
            'rejected' => 'has been rejected',
            default => 'has been updated'
        };

        return (new MailMessage)
            ->subject("Content {$this->moderationQueue->contentVersion->title} {$action}")
            ->line("Content version: {$this->moderationQueue->contentVersion->title}")
            ->line("Status: {$this->moderationQueue->status}")
            ->when($this->moderationQueue->status === 'pending', function ($mail) {
                return $mail->action('Review Content', url("/moderation/{$this->moderationQueue->id}"));
            });
    }

    public function toArray($notifiable): array
    {
        return [
            'moderation_queue_id' => $this->moderationQueue->id,
            'content_version_id' => $this->moderationQueue->content_version_id,
            'status' => $this->moderationQueue->status,
            'message' => "Content version {$this->moderationQueue->contentVersion->title} {$this->getStatusMessage()}",
            'url' => "/moderation/{$this->moderationQueue->id}"
        ];
    }

    protected function getStatusMessage(): string
    {
        return match($this->moderationQueue->status) {
            'pending' => 'requires your approval',
            'approved' => 'has been approved',
            'rejected' => 'has been rejected',
            default => 'status has changed'
        };
    }
}
