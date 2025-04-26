<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'status',
        'published_at'
    ];

    protected $casts = [
        'published_at' => 'datetime'
    ];

    public function translations()
    {
        return $this->hasMany(ContentTranslation::class);
    }

    public function getTranslation($languageCode)
    {
        return $this->translations()
            ->where('language_code', $languageCode)
            ->first();
    }
}
