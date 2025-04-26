<?php

namespace App\Notifications;

use App\Models\DiffComment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class CommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public DiffComment $comment)
    {
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'comment_id' => $this->comment->id,
            'content_id' => $this->comment->content_id,
            'message' => 'New comment on your content',
            'url' => "/content/{$this->comment->content_id}/versions/compare"
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'comment_id' => $this->comment->id,
            'content_id' => $this->comment->content_id,
            'message' => 'New comment on your content',
            'url' => "/content/{$this->comment->content_id}/versions/compare"
        ]);
    }
}