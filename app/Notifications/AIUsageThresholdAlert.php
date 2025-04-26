<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AIUsageThresholdAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public $type;
    public $count;

    public function __construct($type, $count)
    {
        $this->type = $type;
        $this->count = $count;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $subject = $this->type === 'alert' 
            ? 'AI Usage Alert: You have reached your limit' 
            : 'AI Usage Warning: Approaching your limit';

        return (new MailMessage)
            ->subject($subject)
            ->line("You have reached {$this->count} AI operations this month.")
            ->line($this->type === 'alert' 
                ? 'You have reached your maximum allowed AI operations.' 
                : 'You are approaching your AI operations limit.')
            ->action('View Usage Dashboard', url('/dashboard'));
    }

    public function toArray($notifiable)
    {
        return [
            'type' => $this->type,
            'count' => $this->count,
            'message' => $this->type === 'alert'
                ? "You've reached your AI usage limit of {$this->count} operations"
                : "Warning: You're approaching your AI usage limit ({$this->count} operations)"
        ];
    }
}