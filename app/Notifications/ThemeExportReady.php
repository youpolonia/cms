<?php

namespace App\Notifications;

use App\Models\ThemeVersion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ThemeExportReady extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $path,
        protected ThemeVersion $version
    ) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Theme Export Ready: {$this->version->theme->name}")
            ->line("Your export of theme version {$this->version->version} is ready.")
            ->action('Download Export', url("/downloads/{$this->path}"))
            ->line('Thank you for using our application!');
    }

    public function toArray($notifiable)
    {
        return [
            'theme_id' => $this->version->theme_id,
            'version' => $this->version->version,
            'path' => $this->path,
            'message' => "Theme export for version {$this->version->version} is ready"
        ];
    }
}
