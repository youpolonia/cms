<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ContentVersion;

class VersionComparisonNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $version1;
    protected $version2;
    protected $comparisonType;
    protected $stats;

    public function __construct(
        ContentVersion $version1, 
        ContentVersion $version2, 
        string $comparisonType,
        array $stats = []
    ) {
        $this->version1 = $version1;
        $this->version2 = $version2;
        $this->comparisonType = $comparisonType;
        $this->stats = $stats;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject($this->getSubject())
            ->markdown('emails.version-comparison-notification', [
                'version1' => $this->version1,
                'version2' => $this->version2,
                'type' => $this->comparisonType,
                'stats' => $this->stats,
                'user' => $notifiable
            ]);
    }

    public function toArray($notifiable)
    {
        return [
            'version1_id' => $this->version1->id,
            'version2_id' => $this->version2->id,
            'comparison_type' => $this->comparisonType,
            'message' => $this->getMessage(),
            'stats' => $this->stats
        ];
    }

    protected function getSubject(): string
    {
        return match($this->comparisonType) {
            'frequent_change' => "Frequently Compared Version Updated: {$this->version1->title}",
            'cache_update' => "Cached Comparison Updated: {$this->version1->title} vs {$this->version2->title}",
            'new_version' => "New Version Available for Comparison: {$this->version2->title}",
            default => "Version Comparison Notification"
        };
    }

    protected function getMessage(): string
    {
        return match($this->comparisonType) {
            'frequent_change' => "A version you frequently compare ({$this->version1->title}) has been updated.",
            'cache_update' => "A cached comparison between {$this->version1->title} and {$this->version2->title} has been updated.",
            'new_version' => "A new version ({$this->version2->title}) is available for comparison with frequently compared version {$this->version1->title}.",
            default => "Version comparison notification"
        };
    }
}
