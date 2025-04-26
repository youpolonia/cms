<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\Notification;
use Carbon\Carbon;

class NotificationsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $userId;
    protected $dateRange;
    protected $includeRead;
    protected $includeUnread;

    public function __construct($userId, $dateRange, $includeRead, $includeUnread)
    {
        $this->userId = $userId;
        $this->dateRange = $dateRange;
        $this->includeRead = $includeRead;
        $this->includeUnread = $includeUnread;
    }

    public function collection()
    {
        $query = Notification::where('user_id', $this->userId);

        // Apply date range filter
        switch ($this->dateRange) {
            case 'last_week':
                $query->where('created_at', '>=', Carbon::now()->subWeek());
                break;
            case 'last_month':
                $query->where('created_at', '>=', Carbon::now()->subMonth());
                break;
            case 'last_quarter':
                $query->where('created_at', '>=', Carbon::now()->subQuarter());
                break;
        }

        // Apply read status filters
        if ($this->includeRead && !$this->includeUnread) {
            $query->whereNotNull('read_at');
        } elseif (!$this->includeRead && $this->includeUnread) {
            $query->whereNull('read_at');
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Title',
            'Message',
            'Category',
            'Priority',
            'Status',
            'Created At',
            'Read At',
            'URL'
        ];
    }

    public function map($notification): array
    {
        return [
            $notification->title,
            $notification->message,
            ucfirst($notification->category),
            ucfirst($notification->priority),
            $notification->read_at ? 'Read' : 'Unread',
            $notification->created_at->format('Y-m-d H:i:s'),
            $notification->read_at ? $notification->read_at->format('Y-m-d H:i:s') : '',
            $notification->url
        ];
    }
}