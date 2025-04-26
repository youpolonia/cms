<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalWorkflow extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'applicable_content_types'
    ];

    protected $casts = [
        'applicable_content_types' => 'array',
        'is_active' => 'boolean'
    ];

    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalStep::class)->orderBy('order');
    }

    public function isApplicableTo(string $contentType): bool
    {
        if (empty($this->applicable_content_types)) {
            return true;
        }

        return in_array($contentType, $this->applicable_content_types);
    }
}
