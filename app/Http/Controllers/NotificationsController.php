<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $items = $user->notifications()->latest()->take(25)->get()->map(fn ($n) => [
            'id'         => $n->id,
            'title'      => $n->data['title']      ?? 'Notification',
            'message'    => $n->data['message']     ?? '',
            'action_url' => $n->data['action_url']  ?? null,
            'type'       => $n->data['type']        ?? 'info',
            'read'       => ! is_null($n->read_at),
            'time'       => $n->created_at->diffForHumans(),
        ]);

        return response()->json([
            'unread' => $user->unreadNotifications()->count(),
            'items'  => $items,
        ]);
    }

    public function markRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return response()->json(['ok' => true]);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['ok' => true]);
    }
}
