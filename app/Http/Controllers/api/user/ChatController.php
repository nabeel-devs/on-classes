<?php

namespace App\Http\Controllers\api\user;

use App\Models\Chat;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChatController extends Controller
{
    public function index()
    {
        $user = _user();
        $chats = Chat::where('user1_id', $user->id)
            ->orWhere('user2_id', $user->id)
            ->with(['user1', 'user2', 'messages'])
            ->get();

        return response()->json($chats);
    }

    // Create a new chat between two users
    public function store(Request $request)
    {
        $request->validate(['recipient_id' => 'required|exists:users,id']);

        $user = _user();
        $recipientId = $request->recipient_id;

        // Check if a chat already exists
        $chat = Chat::where(function ($query) use ($user, $recipientId) {
            $query->where('user1_id', $user->id)->where('user2_id', $recipientId);
        })->orWhere(function ($query) use ($user, $recipientId) {
            $query->where('user1_id', $recipientId)->where('user2_id', $user->id);
        })->first();

        if (!$chat) {
            $chat = Chat::create(['user1_id' => $user->id, 'user2_id' => $recipientId]);
        }

        return response()->json($chat, 201);
    }

    // Show details of a specific chat
    public function show(Chat $chat)
    {
        $user = _user();

        // Ensure the user is part of the chat
        if ($chat->user1_id !== $user->id && $chat->user2_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $chat->load(['user1', 'user2', 'messages.sender']);
        return response()->json($chat);
    }
}
