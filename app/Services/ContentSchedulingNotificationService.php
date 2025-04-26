<?php

namespace App\Services;

use App\Models\Content;
use App\Models\User;
use App\Notifications\ContentScheduledNotification;
use Carbon\Carbon;

class ContentSchedulingNotificationService
{
    public function notifyUpcomingScheduledContent()
    {
        $threshold = now()->addHours(1);
        
        $contents = Content::where('publish_at', '<=', $threshold)
            ->where('status', 'draft')
            ->get();

        foreach ($contents as $content) {
            $this->sendNotification($content);
        }
    }

    public function notifyPublishedContent()
    {
        $contents = Content::where('publish_at', '<=', now())
            ->where('status', 'draft')
            ->get();

        foreach ($contents as $content) {
            $content->publish();
            $this->sendPublishedNotification($content);
        }
    }

    protected function sendNotification(Content $content)
    {
        $users = User::whereHas('roles', function($query) {
                $query->whereIn('name', ['editor', 'admin']);
            })
            ->get();

        foreach ($users as $user) {
            $user->notify(new ContentScheduledNotification($content));
        }
    }

    protected function sendPublishedNotification(Content $content)
    {
        $content->creator->notify(new ContentPublishedNotification($content));
    }
}