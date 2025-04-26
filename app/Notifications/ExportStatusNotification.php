<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExportStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $status,
        public string $exportType,
        public ?string $downloadUrl = null,
        public ?string $error = null
    ) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->subject("Export {$this->status}: {$this->exportType}");

        if ($this->status === 'completed') {
            $message->line("Your {$this->exportType} export is ready for download.")
                    ->action('Download Export', $this->downloadUrl);
        } else {
            $message->line("Your {$this->exportType} export failed to process.")
                    ->line("Error: {$this->error}")
                    ->line('Please try again or contact support.');
        }

        return $message;
    }

    public function toArray($notifiable)
    {
        return [
            'status' => $this->status,
            'export_type' => $this->exportType,
            'download_url' => $this->downloadUrl,
            'error' => $this->error,
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}