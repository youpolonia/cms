<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Models\AnalyticsExport;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    // Notification preference types
    public const NOTIFICATION_ANALYTICS_EXPORT_READY = 'analytics_export_ready';
    public const NOTIFICATION_ANALYTICS_EXPORT_DELETED = 'analytics_export_deleted';
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'ai_usage_count',
    ];

    protected $casts = [
        'ai_usage_count' => 'integer',
    ];

    public function incrementAiUsage()
    {
        $this->increment('ai_usage_count');
    }

    public function preferences()
    {
        return $this->hasMany(Preference::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function contents()
    {
        return $this->hasMany(Content::class);
    }

    public function analyticsExports()
    {
        return $this->hasMany(AnalyticsExport::class);
    }

    public function mediaCollections()
    {
        return $this->hasMany(MediaCollection::class);
    }
}
