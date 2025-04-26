<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ThemeVersionComparisonStat;

class ThemeComparisonNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The theme version comparison statistics
     *
     * @var ThemeVersionComparisonStat
     */
    public $comparisonStats;

    /**
     * Create a new notification instance.
     *
     * @param ThemeVersionComparisonStat $comparisonStats
     */
    public function __construct(ThemeVersionComparisonStat $comparisonStats)
    {
        $this->comparisonStats = $comparisonStats;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // Check if user has notifications enabled and threshold is met
        $enabled = $notifiable->getNotificationPreference('theme_comparisons', true);
        $threshold = $notifiable->getNotificationPreference('theme_comparison_threshold', 0);
        
        $totalChanges = $this->comparisonStats->lines_added + $this->comparisonStats->lines_removed;
        $changePercentage = $totalChanges > 0 ? 
            ($totalChanges / $this->comparisonStats->total_lines) * 100 : 0;

        if ($enabled && $changePercentage >= $threshold) {
            return ['mail', 'database'];
        }

        return [];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Theme Version Comparison Results')
            ->line('A theme version comparison has been completed.')
            ->line('Theme: ' . $this->comparisonStats->themeVersion->theme->name)
            ->line('Versions compared: ' . $this->comparisonStats->from_version . ' → ' . $this->comparisonStats->to_version)
            ->line('Files changed: ' . $this->comparisonStats->files_changed)
            ->line('Lines added: ' . $this->comparisonStats->lines_added)
            ->line('Lines removed: ' . $this->comparisonStats->lines_removed)
            ->action('View Comparison', url('/themes/versions/compare/' . $this->comparisonStats->id))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'theme_id' => $this->comparisonStats->themeVersion->theme_id,
            'from_version' => $this->comparisonStats->from_version,
            'to_version' => $this->comparisonStats->to_version,
            'files_changed' => $this->comparisonStats->files_changed,
            'lines_added' => $this->comparisonStats->lines_added,
            'lines_removed' => $this->comparisonStats->lines_removed,
            'comparison_url' => '/themes/versions/compare/' . $this->comparisonStats->id,
        ];
    }
}
