<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SharedComparisonNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $comparisonData;

    public function __construct($comparisonData)
    {
        $this->comparisonData = $comparisonData;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Shared Version Comparison: ' . $this->comparisonData['title'])
            ->markdown('emails.shared-comparison', [
                'comparison' => $this->comparisonData
            ]);
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'shared_comparison',
            'title' => $this->comparisonData['title'],
            'url' => $this->comparisonData['url'],
            'shared_by' => $this->comparisonData['shared_by'],
            'shared_at' => $this->comparisonData['shared_at']
        ];
    }
}
