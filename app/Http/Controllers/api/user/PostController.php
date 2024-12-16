<?php

namespace App\Http\Controllers\api\user;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\post\PostResource;
use App\Http\Requests\user\StorePostRequest;
use App\Http\Requests\user\UpdatePostRequest;

class PostController extends Controller
{
    public function index()
{
    $posts = Post::with('media', 'likes', 'comments.user', 'user')->paginate(20);

    if (auth()->check()) {
        // Get the IDs of posts liked and bookmarked by the authenticated user
        $likedPostIds = DB::table('post_likes')
                            ->where('user_id', auth()->id())
                            ->pluck('post_id')
                            ->toArray();

        $bookmarkedPostIds = DB::table('post_bookmarks')
                                ->where('user_id', auth()->id())
                                ->pluck('post_id')
                                ->toArray();

        // Add 'liked_by_auth_user' and 'bookmarked_by_auth_user' to each post
        $posts->getCollection()->transform(function ($post) use ($likedPostIds, $bookmarkedPostIds) {
            $post->liked_by_auth_user = in_array($post->id, $likedPostIds);
            $post->bookmarked_by_auth_user = in_array($post->id, $bookmarkedPostIds);
            return $post;
        });
    }

    return PostResource::collection($posts);
}


    public function userPosts(User $user)
    {
        $posts = Post::with('media', 'likes', 'comments.user', 'user')
            ->where('user_id', $user->id)
            ->paginate(20);

        if (auth()->check()) {
            // Get the IDs of posts liked and bookmarked by the authenticated user
            $likedPostIds = DB::table('post_likes')
                                ->where('user_id', auth()->id())
                                ->pluck('post_id')
                                ->toArray();

            $bookmarkedPostIds = DB::table('post_bookmarks')
                                    ->where('user_id', auth()->id())
                                    ->pluck('post_id')
                                    ->toArray();

            // Add 'liked_by_auth_user' and 'bookmarked_by_auth_user' to each post
            $posts->getCollection()->transform(function ($post) use ($likedPostIds, $bookmarkedPostIds) {
                $post->liked_by_auth_user = in_array($post->id, $likedPostIds);
                $post->bookmarked_by_auth_user = in_array($post->id, $bookmarkedPostIds);
                return $post;
            });
        }

        return PostResource::collection($posts);
    }




    public function store(StorePostRequest $request)
    {
        $post = Post::create(
            $request->except('media') + ['user_id' => _user()->id]
        );

        if ($request->hasFile('media')) {
            $post->addMedia($request->file('media'))->toMediaCollection('posts');
        }

        return new PostResource($post);
    }

    public function show(Post $post)
    {
        // Load the relationships that you need for the post (media, likes, comments, and user)
        $post->load('media', 'likes', 'comments.user', 'user');

        // Check if the user is authenticated and has liked or bookmarked the post
        if (auth()->check()) {
            // Get the liked and bookmarked post IDs for the authenticated user
            $likedPostIds = DB::table('post_likes')
                                ->where('user_id', auth()->id())
                                ->pluck('post_id')
                                ->toArray();

            $bookmarkedPostIds = DB::table('post_bookmarks')
                                    ->where('user_id', auth()->id())
                                    ->pluck('post_id')
                                    ->toArray();

            // Add 'liked_by_auth_user' and 'bookmarked_by_auth_user' attributes
            $post->liked_by_auth_user = in_array($post->id, $likedPostIds);
            $post->bookmarked_by_auth_user = in_array($post->id, $bookmarkedPostIds);
        }

        // Return the post as a resource
        return new PostResource($post);
    }


    public function update(UpdatePostRequest $request, Post $post)
    {
        $post->update($request->except('media'));

        if ($request->hasFile('media')) {
            $post->clearMediaCollection('posts');
            $post->addMedia($request->file('media'))->toMediaCollection('posts');
        }

        return new PostResource($post);
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully.',
        ], 200);
    }

}
