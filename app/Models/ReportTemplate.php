<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportTemplate extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'category',
        'fields',
        'created_by',
        'updated_by'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'fields' => 'array',
        'last_used_at' => 'datetime',
    ];

    /**
     * Get the creator of the template.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the last updater of the template.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get all versions of the template.
     */
    public function versions()
    {
        return $this->hasMany(ReportTemplateVersion::class, 'template_id');
    }

    /**
     * Scope a query to only include templates of a given category.
     */
    public function scopeOfCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to only include templates matching search term.
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /**
     * Create a new version of the template.
     */
    public function createVersion(array $attributes = [])
    {
        return $this->versions()->create(array_merge([
            'version' => $this->getNextVersionNumber(),
            'fields' => $this->fields,
            'created_by' => auth()->id()
        ], $attributes));
    }

    /**
     * Get the next version number.
     */
    protected function getNextVersionNumber()
    {
        $latest = $this->versions()->latest()->first();
        return $latest ? $this->incrementVersion($latest->version) : '1.0';
    }

    /**
     * Increment a version number string.
     */
    protected function incrementVersion(string $version)
    {
        $parts = explode('.', $version);
        $parts[count($parts) - 1]++;
        return implode('.', $parts);
    }

    /**
     * Record template usage.
     */
    public function recordUsage()
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }
}