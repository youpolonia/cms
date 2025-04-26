<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\ContentSchedule;
use App\Models\User;

class SchedulingNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $schedule;
    public $type;
    public $subject;
    public $message;
    public $metadata;

    public function __construct(
        User $user,
        ContentSchedule $schedule,
        string $type,
        string $subject,
        string $message,
        array $metadata = null
    ) {
        $this->user = $user;
        $this->schedule = $schedule;
        $this->type = $type;
        $this->subject = $subject;
        $this->message = $message;
        $this->metadata = $metadata;
    }

    public function build()
    {
        $view = 'emails.scheduling.' . $this->type;
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        return $this->from($fromAddress, $fromName)
            ->subject($this->subject)
            ->markdown($view)
            ->with([
                'user' => $this->user,
                'schedule' => $this->schedule,
                'notificationMessage' => $this->message,
                'metadata' => $this->metadata,
                'unsubscribeUrl' => route('scheduling.notifications.preferences')
            ]);
    }
}