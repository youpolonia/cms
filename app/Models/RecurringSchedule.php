<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecurringSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'frequency',
        'days_of_week',
        'day_of_month',
        'month_of_year',
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime'
    ];

    public function content()
    {
        return $this->belongsTo(Content::class);
    }
}
