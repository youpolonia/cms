<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduledExportRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'scheduled_export_id',
        'status',
        'file_path',
        'error_message'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function export()
    {
        return $this->belongsTo(ScheduledExport::class);
    }
}