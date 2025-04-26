<?php

namespace App\Notifications;

use App\Models\Theme;
use App\Models\ThemeVersion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ThemeInstalled extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Theme $theme,
        public ThemeVersion $version
    ) {}

    public function via($notifiable)
    {
        if (!$notifiable->getNotificationPreference('theme_installed', true)) {
            return [];
        }
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Theme Installed: {$this->theme->name}")
            ->markdown('emails.theme-installed', [
                'theme' => $this->theme,
                'version' => $this->version,
                'actionUrl' => route('themes.versions.show', [
                    'theme' => $this->theme->id,
                    'version' => $this->version->id
                ])
            ]);
    }
}
