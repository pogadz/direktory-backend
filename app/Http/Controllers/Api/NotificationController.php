<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Notifications
 */
class NotificationController extends Controller
{
    /**
     * List notifications for the authenticated user
     *
     * @queryParam read boolean Filter by read status. 1 = read, 0 = unread. Example: 0
     */
    public function index(Request $request)
    {
        $query = $request->user()->notifications();

        if ($request->has('read')) {
            $request->boolean('read')
                ? $query->whereNotNull('read_at')
                : $query->whereNull('read_at');
        }

        $notifications = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'notifications' => $notifications,
            'total'         => $notifications->count(),
            'unread_count'  => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->where('id', $id)->first();

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read']);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All notifications marked as read']);
    }

    /**
     * Delete a notification
     */
    public function destroy(Request $request, string $id)
    {
        $deleted = $request->user()->notifications()->where('id', $id)->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        return response()->json(['message' => 'Notification deleted']);
    }
}
