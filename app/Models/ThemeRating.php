<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThemeRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'theme_id',
        'marketplace_id',
        'marketplace_source',
        'user_id',
        'rating',
        'review'
    ];

    protected static function booted()
    {
        static::creating(function ($rating) {
            if (empty($rating->theme_id)) {
                $rating->validateMarketplaceRating();
            }
        });
    }

    protected function validateMarketplaceRating()
    {
        if (empty($this->marketplace_id)) {
            throw new \InvalidArgumentException('Marketplace ID is required for marketplace ratings');
        }
        if (empty($this->marketplace_source)) {
            throw new \InvalidArgumentException('Marketplace source is required for marketplace ratings');
        }
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
