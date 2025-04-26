<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ErrorClassification extends Model
{
    protected $fillable = [
        'error_category_id',
        'error_message',
        'classified_by',
        'auto_classified',
        'confidence'
    ];

    protected $casts = [
        'auto_classified' => 'boolean',
        'confidence' => 'decimal:2'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ErrorCategory::class, 'error_category_id');
    }

    public function classifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'classified_by');
    }

    public function errorSource(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeAutoClassified($query)
    {
        return $query->where('auto_classified', true);
    }

    public function scopeManualClassified($query)
    {
        return $query->where('auto_classified', false);
    }

    public function scopeHighConfidence($query, $threshold = 0.8)
    {
        return $query->where('confidence', '>=', $threshold);
    }
}