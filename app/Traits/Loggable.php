<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

trait Loggable
{
    public static function bootLoggable()
    {
        static::created(function (Model $model) {
            $model->logAction('created');
        });

        static::updated(function (Model $model) {
            $model->logAction('updated');
        });

        static::deleted(function (Model $model) {
            $model->logAction('deleted');
        });
    }

    public function logAction(string $action, array $metadata = [])
    {
        AuditLog::create([
            'action' => $action,
            'description' => "{$this->getTable()} {$action}",
            'metadata' => $metadata,
            'auditable_type' => get_class($this),
            'auditable_id' => $this->id,
            'user_id' => auth()->id()
        ]);
    }
}