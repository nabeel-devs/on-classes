<?php

namespace App\Http\Controllers\api\user;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MessageController extends Controller
{
    public function index(Chat $chat)
    {
        $user = _user();

        // Ensure the user is part of the chat
        if ($chat->user1_id !== $user->id && $chat->user2_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $messages = $chat->messages()->with('sender')->get();
        return response()->json($messages);
    }

    // Send a message in a chat
    public function store(Request $request, Chat $chat)
    {
        $user = _user();

        // Ensure the user is part of the chat
        if ($chat->user1_id !== $user->id && $chat->user2_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->validate(['content' => 'required|string|max:1000']);

        $message = Message::create([
            'chat_id' => $chat->id,
            'sender_id' => $user->id,
            'content' => $request->content,
        ]);

        return response()->json($message, 201);
    }

    // Mark a message as read
    public function markAsRead(Message $message)
    {
        $user = _user();

        // Ensure the user is part of the chat
        if ($message->chat->user1_id !== $user->id && $message->chat->user2_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $message->update(['is_read' => true]);

        return response()->json(['message' => 'Message marked as read']);
    }
}
