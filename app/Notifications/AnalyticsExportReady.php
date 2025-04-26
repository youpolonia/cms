<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class AnalyticsExportReady extends Notification implements ShouldQueue
{
    use Queueable;

    protected $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function via($notifiable)
    {
        if ($notifiable->getNotificationPreference(User::NOTIFICATION_ANALYTICS_EXPORT_READY)) {
            return ['mail'];
        }
        return [];
    }

    public function toMail($notifiable)
    {
        $url = Storage::url($this->filePath);
        
        return (new MailMessage)
            ->subject('Your Analytics Export is Ready')
            ->markdown('emails.analytics_export', [
                'url' => url($url)
            ]);
    }
}
