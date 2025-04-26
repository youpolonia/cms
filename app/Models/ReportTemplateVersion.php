<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportTemplateVersion extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'template_id',
        'version',
        'fields',
        'notes',
        'created_by'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'fields' => 'array'
    ];

    /**
     * Get the template this version belongs to.
     */
    public function template()
    {
        return $this->belongsTo(ReportTemplate::class);
    }

    /**
     * Get the creator of this version.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Restore this version (make it the current version).
     */
    public function restore()
    {
        $this->template->update([
            'fields' => $this->fields,
            'updated_by' => auth()->id()
        ]);
    }
}