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

    public function destroyMessage(Chat $chat, Message $message)
    {
        $user = _user();

        // Ensure the user is part of the chat
        if ($chat->user1_id !== $user->id && $chat->user2_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Ensure the message belongs to the chat
        if ($message->chat_id !== $chat->id) {
            return response()->json(['message' => 'Message not found in this chat'], 404);
        }

        // Ensure the user is the sender of the message or authorized to delete it
        if ($message->sender_id !== $user->id) {
            return response()->json(['message' => 'You can only delete your own messages'], 403);
        }

        // Delete the message
        $message->delete();

        return response()->json(['message' => 'Message deleted successfully']);
    }

}
