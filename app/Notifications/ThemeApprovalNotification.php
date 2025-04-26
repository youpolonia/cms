<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Theme;
use App\Models\ThemeVersion;

class ThemeApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Theme $theme,
        public ThemeVersion $version,
        public string $actorName,
        public bool $isApproved,
        public ?string $comments = null,
        public ?string $rejectionReason = null,
        public ?string $nextSteps = null
    ) {}

    public function via($notifiable)
    {
        $prefs = $notifiable->notification_preferences['theme_approval'] ?? [
            'enabled' => true
        ];

        return $prefs['enabled'] ? ['mail'] : [];
    }

    public function toMail($notifiable)
    {
        $prefs = $notifiable->notification_preferences['theme_approval'] ?? [
            'includeComments' => true,
            'includeNextSteps' => true
        ];

        $view = $this->isApproved 
            ? 'emails.theme-approved' 
            : 'emails.theme-rejected';

        return (new MailMessage)
            ->subject($this->isApproved 
                ? "Theme Version Approved: {$this->theme->name} (v{$this->version->version})"
                : "Theme Version Rejected: {$this->theme->name} (v{$this->version->version})")
            ->markdown($view, [
                'themeName' => $this->theme->name,
                'version' => $this->version->version,
                'themeId' => $this->theme->id,
                'versionId' => $this->version->id,
                'actorName' => $this->actorName,
                'comments' => $this->comments,
                'rejectionReason' => $this->rejectionReason,
                'nextSteps' => $this->nextSteps,
                'includeComments' => $prefs['includeComments'],
                'includeNextSteps' => $prefs['includeNextSteps']
            ]);
    }
}
