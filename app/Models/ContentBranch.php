<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentBranch extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'name',
        'description',
        'is_default',
        'base_version_id'
    ];

    protected $casts = [
        'is_default' => 'boolean'
    ];

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    public function baseVersion(): BelongsTo
    {
        return $this->belongsTo(ContentVersion::class, 'base_version_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ContentVersion::class, 'branch_name', 'name');
    }

    public function makeDefault(): void
    {
        $this->content->branches()->update(['is_default' => false]);
        $this->update(['is_default' => true]);
    }
}
