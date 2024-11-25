<?php

namespace App\Http\Controllers\api\user;

use App\Models\Post;
use App\Models\PostComment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PostCommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $request->validate([
            'comment' => 'required|string|max:500',
        ]);

        if (!$post) {
            return response()->json(['message' => 'Post not found.'], 404);
        }

        $comment = PostComment::create([
            'post_id' => $post->id,
            'user_id' => _user()->id,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'message' => 'Comment added successfully.',
            'data' => $comment,
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
