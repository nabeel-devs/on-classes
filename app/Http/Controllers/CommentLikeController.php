<?php

namespace App\Http\Controllers;

use App\Models\CommentLike;
use App\Models\PostComment;
use Illuminate\Http\Request;

class CommentLikeController extends Controller
{
    public function store(Request $request, PostComment $comment)
    {
        $request->validate([
            'is_liked' => 'required|boolean',
        ]);

        $user = _user();
        $like = CommentLike::where('post_comment_id', $comment->id)
                    ->where('user_id', _user()->id)
                    ->first();

        if ($like) {
            // If a like already exists, update the is_liked status
            $like->is_liked = $request->is_liked;
            $like->save();
        } else {
            $like = CommentLike::create([
                'post_comment_id' => $comment->id,
                'user_id' => _user()->id,
                'is_liked' => $request->is_liked,
            ]);
        }

        // Count total likes for the comment
        $likeCount = CommentLike::where('post_comment_id', $comment->id)
                            ->where('is_liked', true)
                            ->count();

        return response()->json([
            'message' => 'Comment like status updated successfully.',
            'data' => $like,
            'like_count' => $likeCount,
        ], 200);
    }


}
