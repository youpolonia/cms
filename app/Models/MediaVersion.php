<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaVersion extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = $model->id ?: (string) \Illuminate\Support\Str::uuid();
        });
    }

    protected $fillable = [
        'media_id',
        'user_id',
        'version_number',
        'filename',
        'path',
        'metadata',
        'changes',
        'comment',
        'created_by',
        'branch_name',
        'parent_version_id',
        'is_merged',
        'merged_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'changes' => 'array',
    ];

    protected $appends = ['version_label', 'file_size', 'mime_type'];

    public function media()
    {
        return $this->belongsTo(Media::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parentVersion()
    {
        return $this->belongsTo(MediaVersion::class, 'parent_version_id', 'id');
    }

    public function childVersions()
    {
        return $this->hasMany(MediaVersion::class, 'parent_version_id');
    }

    public function branch()
    {
        return $this->belongsTo(MediaBranch::class, 'branch_name', 'name');
    }

    public function mergeIntoBranch(string $branchName): bool
    {
        $branch = MediaBranch::where('name', $branchName)
            ->where('media_id', $this->media_id)
            ->first();

        if (!$branch) {
            return false;
        }

        $newVersion = $this->media->createVersion(
            $this->changes,
            "Merged from version {$this->version_number}",
            $branchName,
            $branch->base_version_id
        );

        return $newVersion !== null;
    }

    public function getVersionLabelAttribute()
    {
        $label = "v{$this->version_number}";
        if ($this->branch_name) {
            $label .= " ({$this->branch_name})";
        }
        return $label;
    }

    public function getFileSizeAttribute()
    {
        return $this->metadata['size'] ?? 0;
    }

    public function getMimeTypeAttribute()
    {
        return $this->metadata['mime_type'] ?? 'unknown';
    }

    public function isOnBranch()
    {
        return !empty($this->branch_name);
    }
}
