<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    /**
     * Display user's notifications
     */
    public function index(Request $request)
    {
        $notifications = auth()->user()->notifications()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->find($id);
        
        if ($notification) {
            $notification->markAsRead();
        }

        // Handle Inertia requests vs AJAX requests
        if (request()->header('X-Inertia')) {
            return back();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        // Handle Inertia requests vs AJAX requests
        if (request()->header('X-Inertia')) {
            return back();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get unread notifications count for header
     */
    public function getUnreadCount()
    {
        $count = auth()->user()->unreadNotifications()->count();
        
        return response()->json(['count' => $count]);
    }

    /**
     * Get recent notifications for dropdown
     */
    public function getRecent()
    {
        $notifications = auth()->user()->notifications()
            ->take(5)
            ->get();

        return response()->json(['notifications' => $notifications]);
    }
}
