<?php

namespace App\Http\Controllers\api\user;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function getNotifications(Request $request)
    {
        $user = Auth::user();
        $notifications = $user->notifications()->paginate(10);

        return response()->json([
            'notifications' => $notifications
        ]);
    }

    public function markAsRead($notificationId)
    {
        $user = Auth::user();

        // Find the notification
        $notification = $user->notifications()->findOrFail($notificationId);

        // Mark the notification as read
        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read.',
            'notification' => $notification
        ]);
    }

    public function markAllAsRead()
    {
        $user = Auth::user();

        // Mark all notifications as read
        $user->unreadNotifications->markAsRead();

        return response()->json([
            'message' => 'All notifications marked as read.'
        ]);
    }

    public function getUnreadNotifications(Request $request)
    {
        $user = Auth::user();

        // Get unread notifications
        $unreadNotifications = $user->unreadNotifications()->paginate(10);

        return response()->json([
            'unread_notifications' => $unreadNotifications
        ]);
    }



}
