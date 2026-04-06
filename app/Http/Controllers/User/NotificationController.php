<?php
// app/Http/Controllers/User/NotificationController.php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        // Get the current authenticated user ID
        $userId = Auth::id();

        Log::info('Notification index called for user: ' . $userId);

        $query = Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        // Filter by read/unread
        if ($request->filter === 'unread') {
            $query->where('is_read', false);
        } elseif ($request->filter === 'read') {
            $query->where('is_read', true);
        }

        $notifications = $query->paginate(20);

        Log::info('Notifications found: ' . $notifications->total());

        return Inertia::render('Users/Notifications/Index', [
            'notifications' => $notifications,
            'unreadCount' => Notification::where('user_id', $userId)
                ->where('is_read', false)
                ->count(),
            'filter' => $request->get('filter', 'all'),
        ]);
    }

    public function markAsRead($id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $notification->update(['is_read' => true]);

        return back()->with('success', 'Notification marked as read');
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back()->with('success', 'All notifications marked as read');
    }

    public function destroy($id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $notification->delete();

        return back()->with('success', 'Notification deleted');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:notifications,id',
        ]);

        Notification::where('user_id', Auth::id())
            ->whereIn('id', $request->ids)
            ->delete();

        return back()->with('success', 'Notifications deleted');
    }

    public function clearAll()
    {
        Notification::where('user_id', Auth::id())->delete();

        return back()->with('success', 'All notifications cleared');
    }
}
