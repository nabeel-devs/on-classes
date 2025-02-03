<?php

namespace App\Http\Controllers\api\user;

use App\Models\Post;
use App\Models\PostComment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\user\CommentResource;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PostCommentNotification;

class PostCommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $request->validate([
            'comment' => 'required|string|max:500',
            'comment_id' => 'nullable|exists:post_comments,id', // Validate comment_id for replies
        ]);

        if (!$post) {
            return response()->json(['message' => 'Post not found.'], 404);
        }

        $user = _user();

        $comment = PostComment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'comment' => $request->comment,
            'comment_id' => $request->comment_id, // Store parent comment ID if it's a reply
        ]);

        // Notify post owner or parent comment owner
        if ($request->comment_id) {
            $parentComment = PostComment::find($request->comment_id);
            if ($parentComment) {
                Notification::send($parentComment->user, new PostCommentNotification($post, $user));
            }
        } else {
            Notification::send($post->user, new PostCommentNotification($post, $user));
        }

        return response()->json([
            'message' => 'Comment added successfully.',
            'data' => new CommentResource($comment->load('user', 'replies')),
        ], 201);
    }


    // Delete a comment
    public function destroy(PostComment $comment)
    {
        // Check if the authenticated user owns the comment or is an admin
        if ($comment->user_id !== _user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully.',
        ]);
    }
}
