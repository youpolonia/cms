<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'subject',
        'content',
        'variables',
        'notification_type',
        'is_system',
        'is_active'
    ];

    protected $casts = [
        'variables' => 'array',
        'is_system' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForType($query, $type)
    {
        return $query->where('notification_type', $type);
    }

    public function renderContent(array $data = [])
    {
        $content = $this->content;
        
        foreach ($data as $key => $value) {
            $content = str_replace("{{{$key}}}", $value, $content);
        }

        return $content;
    }

    public function renderSubject(array $data = [])
    {
        $subject = $this->subject;
        
        foreach ($data as $key => $value) {
            $subject = str_replace("{{{$key}}}", $value, $subject);
        }

        return $subject;
    }
}