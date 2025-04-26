<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

class AnalyticsExportFailed extends Notification implements ShouldQueue
{
    use Queueable;

    protected $error;

    public function __construct($error = null)
    {
        $this->error = $error;
    }

    public function via($notifiable)
    {
        if ($notifiable->getNotificationPreference(User::NOTIFICATION_ANALYTICS_EXPORT_FAILED)) {
            return ['mail'];
        }
        return [];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Analytics Export Failed')
            ->markdown('emails.analytics_export_failed', [
                'error' => $this->error
            ]);
    }
}