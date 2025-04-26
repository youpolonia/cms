<?php

namespace App\Events;

use App\Models\OperationNotification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OperationNotificationEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notification;

    public function __construct(OperationNotification $notification)
    {
        $this->notification = $notification;
    }

    public function broadcastOn()
    {
        return new Channel('user.'.$this->notification->user_id);
    }

    public function broadcastAs()
    {
        return 'operation.notification';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->notification->id,
            'type' => $this->notification->type,
            'status' => $this->notification->status,
            'message' => $this->notification->message,
            'created_at' => $this->notification->created_at->toDateTimeString()
        ];
    }
}