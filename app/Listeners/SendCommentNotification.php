<?php

namespace App\Listeners;

use App\Events\CommentCreated;
use App\Notifications\CommentNotification;

class SendCommentNotification
{
    public function handle(CommentCreated $event)
    {
        // Get users to notify (content author, thread participants)
        $users = $this->getUsersToNotify($event->comment);
        
        // Send notifications
        foreach ($users as $user) {
            $user->notify(new CommentNotification($event->comment));
        }
    }

    protected function getUsersToNotify($comment)
    {
        // TODO: Implement logic to get relevant users
        return collect();
    }
}