<?php

namespace App\Notifications;

use App\Models\AnalyticsExport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ExportExpiringSoon extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public AnalyticsExport $export,
        public int $daysRemaining
    ) {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Export Expiring in {$this->daysRemaining} Day(s)")
            ->line("Your analytics export for {$this->export->start_date->format('M j, Y')} to {$this->export->end_date->format('M j, Y')} will expire soon.")
            ->line("Expiration date: {$this->export->expires_at->format('M j, Y')}")
            ->action('Download Now', route('exports.download', $this->export))
            ->line('Thank you for using our analytics system!');
    }
}