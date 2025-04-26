<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSoundSettings extends Model
{
    protected $fillable = [
        'user_id',
        'enabled',
        'volume',
        'sound',
        'mute_duration'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
