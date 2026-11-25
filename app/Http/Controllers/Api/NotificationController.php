<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return [
            'unread_count'  => $request->user()->unreadNotifications()->count(),
            'notifications' => $request->user()->notifications()->latest()->limit(50)->get()
                ->map(fn ($n) => [
                    'id' => $n->id, 'title' => $n->data['title'] ?? null, 'body' => $n->data['body'] ?? null,
                    'production_record_id' => $n->data['production_record_id'] ?? null,
                    'read_at' => $n->read_at, 'created_at' => $n->created_at,
                ]),
        ];
    }

    public function read(Request $request, string $id)
    {
        $request->user()->notifications()->whereKey($id)->firstOrFail()->markAsRead();
        return response()->json(['message' => 'Marked as read.']);
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'All notifications marked as read.']);
    }
}
