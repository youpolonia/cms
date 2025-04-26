<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduledExport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'frequency_hours',
        'anonymization_options',
        'last_run_at',
        'is_active'
    ];

    protected $casts = [
        'anonymization_options' => 'array',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function runs()
    {
        return $this->hasMany(ScheduledExportRun::class);
    }
}