<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ErrorResolutionStep extends Model
{
    protected $fillable = [
        'name',
        'description',
        'handler_class',
        'handler_method',
        'order',
        'is_required',
        'workflow_id'
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(ErrorResolutionWorkflow::class);
    }

    public function execute($error)
    {
        $handler = app($this->handler_class);
        return $handler->{$this->handler_method}($error);
    }
}