<?php

namespace App\Jobs;

use App\Models\Content;
use App\Models\User;
use App\Notifications\ContentPublishedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ContentSchedulingNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Content $content
    ) {}

    public function handle()
    {
        // Get users who should be notified (admins, content managers, etc.)
        $users = User::role(['admin', 'content-manager'])->get();

        // Send notifications
        foreach ($users as $user) {
            $user->notify(new ContentPublishedNotification($this->content));
        }
    }
}