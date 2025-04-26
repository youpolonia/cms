<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'language_code',
        'translated_content'
    ];

    protected $casts = [
        'translated_content' => 'json'
    ];

    public function content()
    {
        return $this->belongsTo(Content::class);
    }
}