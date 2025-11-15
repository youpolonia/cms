<?php
class ScheduleNotification {
    const TYPE_CREATED = 'created';
    const TYPE_UPDATED = 'updated';
    const TYPE_EXECUTING = 'executing';
    const TYPE_COMPLETED = 'completed';
    const TYPE_CONFLICT = 'conflict';

    public $schedule_id;
    public $notification_id;
    public $worker_id;
    public $title;
    public $message;
    public $type;
    public $scheduled_at;
    public $status;
    public $retry_count;
    public $max_retries;
    public $last_attempt;

    public static function getTypes(): array {
        return [
            self::TYPE_CREATED,
            self::TYPE_UPDATED,
            self::TYPE_EXECUTING,
            self::TYPE_COMPLETED,
            self::TYPE_CONFLICT
        ];
    }
}
