<?php

namespace App\Notifications;

use App\Models\Theme;
use App\Models\ThemeVersion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ThemeUpdateInstalled extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Theme $theme,
        public ThemeVersion $version,
        public bool $isInstallation = false,
        public ?string $error = null
    ) {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        if ($this->error) {
            return $this->buildFailureMail();
        }

        return $this->buildSuccessMail();
    }

    protected function buildSuccessMail()
    {
        $subject = $this->isInstallation 
            ? "Theme Successfully Installed: {$this->theme->name}"
            : "Theme Update Installed: {$this->theme->name}";

        $template = $this->isInstallation
            ? 'emails.theme-installed'
            : 'emails.theme-update-installed';

        return (new MailMessage)
            ->subject($subject)
            ->markdown($template, [
                'theme' => $this->theme,
                'version' => $this->version,
                'actionUrl' => route('themes.versions.show', [
                    'theme' => $this->theme->id,
                    'version' => $this->version->id
                ])
            ]);
    }

    protected function buildFailureMail()
    {
        return (new MailMessage)
            ->subject("Theme Installation Failed: {$this->theme->name}")
            ->markdown('emails.theme-install-failed', [
                'theme' => $this->theme,
                'version' => $this->version,
                'error' => $this->error,
                'actionUrl' => route('themes.updates')
            ]);
    }
}
