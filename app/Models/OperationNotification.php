<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'status',
        'message',
        'metadata',
        'user_id',
        'completed_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'completed_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsCompleted(string $message = null)
    {
        $this->update([
            'status' => 'completed',
            'message' => $message ?? $this->message,
            'completed_at' => now()
        ]);
    }

    public function markAsFailed(string $error)
    {
        $this->update([
            'status' => 'failed',
            'message' => $error,
            'completed_at' => now()
        ]);
    }
}