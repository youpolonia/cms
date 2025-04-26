<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExportHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'template_id',
        'status',
        'file_path',
        'file_size',
        'recipient_count',
        'error_log',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    public function schedule()
    {
        return $this->belongsTo(ScheduledExport::class);
    }

    public function template()
    {
        return $this->belongsTo(ReportTemplate::class);
    }

    public function getDurationAttribute()
    {
        return $this->completed_at 
            ? $this->started_at->diffInSeconds($this->completed_at)
            : null;
    }

    public function getFileSizeFormattedAttribute()
    {
        if (!$this->file_size) return null;
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;
        
        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }
        
        return round($size, 2) . ' ' . $units[$unit];
    }
}