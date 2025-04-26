<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationArchiveController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $perPage = $request->input('per_page', 15);
        $filters = $request->input('filters', []);

        $query = $user->notifications()
            ->with('notifiable')
            ->orderBy('created_at', 'desc');

        // Apply filters
        foreach ($filters as $filter) {
            if ($filter['is_active'] && isset($filter['type']) && isset($filter['value'])) {
                switch ($filter['type']) {
                    case 'priority':
                        $query->where('priority', $filter['value']);
                        break;
                    case 'category':
                        $query->where('category', $filter['value']);
                        break;
                    case 'read_status':
                        if ($filter['value'] === 'read') {
                            $query->whereNotNull('read_at');
                        } else {
                            $query->whereNull('read_at');
                        }
                        break;
                    case 'date_range':
                        $query->whereBetween('created_at', $this->getDateRange($filter['value']));
                        break;
                }
            }
        }

        $notifications = $query->paginate($perPage);

        return response()->json([
            'notifications' => $notifications,
            'total_count' => $user->notifications()->count(),
            'unread_count' => $user->unreadNotifications()->count()
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read',
            'notification' => $notification->fresh()
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json([
            'message' => 'All notifications marked as read',
            'unread_count' => 0
        ]);
    }

    private function getDateRange($range)
    {
        $now = now();
        
        switch ($range) {
            case 'today':
                return [$now->startOfDay(), $now->copy()->endOfDay()];
            case 'this_week':
                return [$now->startOfWeek(), $now->copy()->endOfWeek()];
            case 'this_month':
                return [$now->startOfMonth(), $now->copy()->endOfMonth()];
            default:
                return [now()->subDays(30), now()];
        }
    }
}