<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Notification, Guru};
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get All Notifications
     */
    public function index(Request $request)
    {
        $guru = Guru::where('user_id', Auth::id())->first();
        
        if (!$guru) {
            return response()->json([
                'success' => false,
                'message' => 'Guru not found',
            ], 404);
        }

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 20);

        $notifications = Notification::where('guru_id', $guru->id)
                                    ->orderBy('created_at', 'desc')
                                    ->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $notifications->items(),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'total_pages' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    /**
     * Get Unread Count
     */
    public function unreadCount()
    {
        $guru = Guru::where('user_id', Auth::id())->first();
        
        if (!$guru) {
            return response()->json([
                'success' => false,
                'message' => 'Guru not found',
            ], 404);
        }

        $count = Notification::where('guru_id', $guru->id)
                            ->where('is_read', false)
                            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $count,
        ]);
    }

    /**
     * Mark as Read
     */
    public function markAsRead($id)
    {
        $guru = Guru::where('user_id', Auth::id())->first();
        
        if (!$guru) {
            return response()->json([
                'success' => false,
                'message' => 'Guru not found',
            ], 404);
        }

        $notification = Notification::where('guru_id', $guru->id)
                                   ->where('id', $id)
                                   ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }

        $notification->update(['is_read' => true, 'read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark All as Read
     */
    public function markAllAsRead()
    {
        $guru = Guru::where('user_id', Auth::id())->first();
        
        if (!$guru) {
            return response()->json([
                'success' => false,
                'message' => 'Guru not found',
            ], 404);
        }

        Notification::where('guru_id', $guru->id)
                   ->where('is_read', false)
                   ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }
}
