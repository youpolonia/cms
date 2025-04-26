<?php

namespace App\Notifications;

use App\Models\Theme;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ThemeUpdateAvailable extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Theme $theme,
        public string $currentVersion,
        public string $newVersion,
        public string $changelog,
        public array $dependencyIssues = []
    ) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Update Available for Theme: {$this->theme->name}")
            ->markdown('emails.theme-update', [
                'theme' => $this->theme,
                'currentVersion' => $this->currentVersion,
                'newVersion' => $this->newVersion,
                'changelog' => $this->changelog,
                'dependencyIssues' => $this->dependencyIssues
            ]);
    }

    public function toArray($notifiable)
    {
        return [
            'theme_id' => $this->theme->id,
            'theme_name' => $this->theme->name,
            'current_version' => $this->currentVersion,
            'new_version' => $this->newVersion,
            'message' => "New version {$this->newVersion} available for theme {$this->theme->name}",
            'dependency_issues' => $this->dependencyIssues
        ];
    }
}
