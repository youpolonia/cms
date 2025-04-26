<?php

namespace App\Http\Controllers;

use App\Models\NotificationHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = NotificationHistory::forUser(Auth::id())
            ->recent()
            ->orderBy('created_at', 'desc');

        if ($request->has('unread')) {
            $query->unread();
        }

        if ($request->has('type')) {
            $query->where('notification_type', $request->type);
        }

        return response()->json(
            $query->paginate($request->per_page ?? 15)
        );
    }

    public function markAsRead($id)
    {
        $notification = NotificationHistory::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $notification->update(['read_at' => now()]);

        return response()->json($notification);
    }

    public function markAllAsRead()
    {
        NotificationHistory::forUser(Auth::id())
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read']);
    }

    public function unreadCount()
    {
        $count = NotificationHistory::forUser(Auth::id())
            ->unread()
            ->count();

        return response()->json(['count' => $count]);
    }
}