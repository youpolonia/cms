<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Theme;

class ThemeUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Theme $theme,
        public string $version
    ) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Theme Update Available: {$this->theme->name} v{$this->version}")
            ->markdown('emails.theme-update', [
                'theme' => $this->theme,
                'version' => $this->version,
                'user' => $notifiable
            ]);
    }

    public function toArray($notifiable)
    {
        return [
            'theme_id' => $this->theme->id,
            'version' => $this->version,
            'message' => "New update available for {$this->theme->name} (v{$this->version})"
        ];
    }
}
