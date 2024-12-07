<?php

namespace App\Http\Controllers\api\user;

use App\Models\Post;
use App\Models\PostLike;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PostLikeController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $request->validate([
            'is_liked' => 'required|boolean',
        ]);

        $like = PostLike::where('post_id', $post->id)
                    ->where('user_id', _user()->id)
                    ->first();

        if ($like) {
            // If a like already exists, update the is_liked status
            $like->is_liked = $request->is_liked;
            $like->save();

            return response()->json([
                'message' => 'Post like status updated successfully.',
                'data' => $like,
            ], 200);
        } else {
            $like = PostLike::create([
                'post_id' => $post->id,
                'user_id' => _user()->id,
                'is_liked' => $request->is_liked,
            ]);

            return response()->json([
                'message' => 'Post liked successfully.',
                'data' => $like,
            ], 201);
        }
    }


    // Remove a like from a post
    public function destroy(PostLike $like)
    {
        // Check if the authenticated user owns the like
        if ($like->user_id !== _user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $like->delete();

        return response()->json([
            'message' => 'Like removed successfully.',
        ]);
    }
}