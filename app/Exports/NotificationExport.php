<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\NotificationArchive;

class NotificationExport implements FromQuery, WithHeadings, WithMapping
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'Type',
            'Subject',
            'Content',
            'Created At',
            'Read At',
            'Priority'
        ];
    }

    public function map($notification): array
    {
        return [
            $notification->notification_type,
            $notification->subject,
            $notification->content,
            $notification->created_at->format('Y-m-d H:i:s'),
            $notification->read_at ? $notification->read_at->format('Y-m-d H:i:s') : 'Unread',
            $notification->priority
        ];
    }
}