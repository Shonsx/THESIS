<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class NotificationController extends Controller
{
    public function index()
    {
        // Fetch notifications for the authenticated user
        $notifications = Auth::user()->notifications;

        // Pass them to the view
        return view('notifications.index', compact('notifications'));
    }

    // Optionally, mark notifications as read
    public function markAsRead($id)
    {
        $notification = Auth::user()->unreadNotifications->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return back();
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    
}
