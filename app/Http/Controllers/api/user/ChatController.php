<?php

namespace App\Http\Controllers\api\user;

use App\Models\Chat;
use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\user\UserResource;

class ChatController extends Controller
{
    public function index()
    {
        $user = _user();

        $chats = Chat::where('accepted', true)
            ->where('archived', false)
            ->where(function ($query) use ($user) {
                $query->where('user1_id', $user->id)
                    ->orWhere('user2_id', $user->id);
            })
            ->with(['user1', 'user2', 'messages.media' => function ($query) {
                $query->orderBy('created_at', 'desc');  // Order messages by latest
            }])
            ->get();

        return response()->json([
            'chats' => $chats->map(function ($chat) {
                return [
                    'id' => $chat->id,
                    'user1' => new UserResource($chat->user1),
                    'user2' => new UserResource($chat->user2),
                    'messages' => $chat->messages,
                    'created_at' => $chat->created_at,
                ];
            }),
        ]);
    }



    public function requestChats()
    {
        $user = _user();

        $chats = Chat::where('accepted', false)
            ->where(function ($query) use ($user) {
                $query->where('user1_id', $user->id)
                    ->orWhere('user2_id', $user->id);
            })
            ->with([
                'user1',
                'user2',
                'messages.media' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'messages.audio' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                }
            ])
            ->get();

        return response()->json([
            'chats' => $chats->map(function ($chat) {
                return [
                    'id' => $chat->id,
                    'user1' => new UserResource($chat->user1),
                    'user2' => new UserResource($chat->user2),
                    'messages' => $chat->messages,
                    'created_at' => $chat->created_at,
                ];
            }),
        ]);
    }


    public function archivedChats()
    {
        $user = _user();

        $chats = Chat::where('accepted', true)
            ->where('archived', true)
            ->where(function ($query) use ($user) {
                $query->where('user1_id', $user->id)
                    ->orWhere('user2_id', $user->id);
            })
            ->with([
                'user1',
                'user2',
                'messages.media' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'messages.audio' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                }
            ])
            ->get();

        return response()->json([
            'chats' => $chats->map(function ($chat) {
                return [
                    'id' => $chat->id,
                    'user1' => new UserResource($chat->user1),
                    'user2' => new UserResource($chat->user2),
                    'messages' => $chat->messages,
                    'created_at' => $chat->created_at,
                ];
            }),
        ]);
    }

    public function acceptRequest(Request $request, $chatId)
    {
        $user = _user();
        $chat = Chat::where('id', $chatId)
                    ->where(function ($query) use ($user) {
                        $query->where('user1_id', $user->id)
                            ->orWhere('user2_id', $user->id);
                    })
                    ->firstOrFail();

        // Update the 'accepted' status
        $chat->accepted = $request->accepted;
        $chat->save();

        // Determine the response message based on the 'accepted' value
        $message = $chat->accepted ? 'Request accepted' : 'Request rejected';

        return response()->json(['message' => $message, 'chat' => $chat]);
    }


    public function setArchived(Request $request, $chatId)
    {
        $user = _user();
        $chat = Chat::where('id', $chatId)
                    ->where(function ($query) use ($user) {
                        $query->where('user1_id', $user->id)
                            ->orWhere('user2_id', $user->id);
                    })
                    ->firstOrFail();

        $chat->archived = $request->archived;
        $chat->save();

        return response()->json(['message' => 'Archived status updated', 'chat' => $chat]);
    }



    // Create a new chat between two users
    public function store(Request $request)
    {
        $request->validate(['recipient_id' => 'required|exists:users,id']);

        $user = _user();
        $recipientId = $request->recipient_id;

        // Check if a chat already exists
        $chat = Chat::where(function ($query) use ($user, $recipientId) {
                $query->where('user1_id', $user->id)
                    ->where('user2_id', $recipientId);
            })
            ->orWhere(function ($query) use ($user, $recipientId) {
                $query->where('user1_id', $recipientId)
                    ->where('user2_id', $user->id);
            })
            ->first();

        if (!$chat) {
            // Check if the user follows the recipient or vice versa
            $follows = Follow::where(function ($query) use ($user, $recipientId) {
                $query->where('follower_id', $user->id)
                    ->where('following_id', $recipientId);
            })
            ->orWhere(function ($query) use ($user, $recipientId) {
                $query->where('follower_id', $recipientId)
                    ->where('following_id', $user->id);
            })
            ->exists();

            // Create chat and set accepted based on follow status
            $chat = Chat::create([
                'user1_id' => $user->id,
                'user2_id' => $recipientId,
                'accepted' => $follows ? true : false,
            ]);
        }

        return response()->json($chat, 201);
    }


    public function show(Chat $chat)
{
    $user = _user();

    // Ensure the user is part of the chat
    if ($chat->user1_id !== $user->id && $chat->user2_id !== $user->id) {
        return response()->json(['message' => 'Forbidden'], 403);
    }

    // Load chat users and messages
    $chat->load(['user1', 'user2', 'messages']);

    return response()->json([
        'chats' => [
            [
                'id' => $chat->id,
                'user1' => new UserResource($chat->user1),
                'user2' => new UserResource($chat->user2),
                'messages' => $chat->messages->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'content' => $message->content,
                        'sender_id' => $message->sender_id,
                        'chat_id' => $message->chat_id,
                        'media' => $message->getMedia('media')->map(fn($media) => $media->getUrl()),
                        'audio' => $message->getMedia('audio')->map(fn($media) => $media->getUrl()),
                        'created_at' => $message->created_at,
                    ];
                }),
                'created_at' => $chat->created_at,
            ]
        ]
    ]);
}



    public function quickChat()
    {
        $onlineUsers = User::where('online', true)
            ->where('id', '!=', _user()->id)
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        return UserResource::collection($onlineUsers);
    }

    public function userList()
    {
        $onlineUsers = User::where('id', '!=', _user()->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        return UserResource::collection($onlineUsers);
    }

    public function destroy(Chat $chat)
    {
        $user = _user();

        // Ensure the user is part of the chat
        if ($chat->user1_id !== $user->id && $chat->user2_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Delete the chat
        $chat->delete();

        return response()->json(['message' => 'Chat deleted successfully']);
    }


}
