<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

class AnalyticsExportDeleted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $fileName;

    public function __construct($fileName)
    {
        $this->fileName = $fileName;
    }

    public function via($notifiable)
    {
        if ($notifiable->getNotificationPreference(User::NOTIFICATION_ANALYTICS_EXPORT_DELETED)) {
            return ['mail'];
        }
        return [];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Analytics Export Has Been Deleted')
            ->markdown('emails.analytics_export_deleted', [
                'fileName' => $this->fileName
            ]);
    }
}
