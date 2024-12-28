<?php

namespace App\Http\Controllers\api\user;

use App\Models\Post;
use App\Models\PostBookmark;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PostBookmarkController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $request->validate([
            'is_bookmarked' => 'required|boolean',
        ]);

        $bookmark = PostBookmark::where('post_id', $post->id)
                    ->where('user_id', _user()->id)
                    ->first();

        if ($bookmark) {
            $bookmark->is_bookmarked = $request->is_bookmarked;
            $bookmark->save();
        } else {
            $bookmark = PostBookmark::create([
                'post_id' => $post->id,
                'user_id' => _user()->id,
                'is_bookmarked' => $request->is_bookmarked,
            ]);
        }

        // Count total bookmarks for the post
        $bookmarkCount = PostBookmark::where('post_id', $post->id)
                                    ->where('is_bookmarked', true)
                                    ->count();

        return response()->json([
            'message' => 'Post bookmark status updated successfully.',
            'data' => $bookmark,
            'bookmark_count' => $bookmarkCount,
        ], 200);
    }


    // Remove a bookmark from a post
    public function destroy(PostBookmark $bookmark)
    {
        // Check if the authenticated user owns the bookmark
        if ($bookmark->user_id !== _user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $bookmark->delete();

        return response()->json([
            'message' => 'Bookmark removed successfully.',
        ]);
    }
}
