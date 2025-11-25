<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function markAsRead(Request $request, string $id)
    {
        $user = $request->user();
        $notification = $user->notifications()->where('id', $id)->firstOrFail();
        if (!$notification->read_at) {
            $notification->markAsRead();
        }
        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $unread = $user->unreadNotifications()->get();
        foreach ($unread as $n) {
            $n->markAsRead();
        }
        return redirect()->back()->with('success', 'All notifications marked as read.');
    }
}