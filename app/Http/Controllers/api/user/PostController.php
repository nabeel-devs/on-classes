<?php

namespace App\Http\Controllers\api\user;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Jobs\NewPostNotificationJob;
use Illuminate\Support\Facades\File;
use App\Http\Resources\post\PostResource;
use App\Http\Requests\user\StorePostRequest;
use App\Http\Requests\user\UpdatePostRequest;
use App\Http\Resources\post\UserStoryResource;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with([
            'media',
            'likes',
            'comments.user',
            'comments.likes',
            'comments.replies.user',
            'comments.replies.likes',
            'user'
        ])
        ->where('is_story', false)
        ->where('type', '!=', 'reel')
        ->orderBy('created_at', 'desc')
        ->paginate(20);

        if (auth()->check()) {
            $authUserId = auth()->id();

            // Get liked and bookmarked post IDs
            $likedPostIds = DB::table('post_likes')
                ->where('is_liked', true)
                ->where('user_id', $authUserId)
                ->pluck('post_id')
                ->toArray();

            $bookmarkedPostIds = DB::table('post_bookmarks')
                ->where('is_bookmarked', true)
                ->where('user_id', $authUserId)
                ->pluck('post_id')
                ->toArray();

            // Transform each post in the collection
            $posts->getCollection()->transform(function ($post) use ($likedPostIds, $bookmarkedPostIds) {
                if (!($post instanceof \App\Models\Post)) {
                    throw new \Exception('Expected Post instance but got ' . get_class($post));
                }

                $post->liked_by_auth_user = in_array($post->id, $likedPostIds);
                $post->bookmarked_by_auth_user = in_array($post->id, $bookmarkedPostIds);

                return $post;
            });
        }

        // Return paginated posts wrapped in PostResource
        return PostResource::collection($posts);
    }


    public function allPosts()
    {
        $posts = Post::with([
            'media',
            'likes',
            'comments.user',
            'comments.likes',
            'comments.replies.user',
            'comments.replies.likes',
            'user'
        ])
        ->where('is_story', false)
        ->where('type', '!=', 'reel')
        ->orderBy('created_at', 'desc')
        ->paginate(20);



        // Return paginated posts wrapped in PostResource
        return PostResource::collection($posts);
    }


    public function userNonAuthPosts(User $user)
    {
        $posts = Post::with([
            'media',
            'likes',
            'comments.user',
            'comments.likes',
            'comments.replies.user',
            'comments.replies.likes',
            'user'
        ])
            ->where('user_id', $user->id)
            ->where('is_story', false)
            ->where('type', '!=', 'reel')
            ->orderBy('created_at', 'desc')
            ->paginate(20);


        return PostResource::collection($posts);
    }



    public function userPosts(User $user)
    {
        $posts = Post::with([
            'media',
            'likes',
            'comments.user',
            'comments.likes',
            'comments.replies.user',
            'comments.replies.likes',
            'user'
        ])
            ->where('user_id', $user->id)
            ->where('is_story', false)
            ->where('type', '!=', 'reel')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

            if (auth()->check()) {
                $authUserId = auth()->id();

                $likedPostIds = DB::table('post_likes')
                    ->where('is_liked', true)
                    ->where('user_id', $authUserId)
                    ->pluck('post_id')
                    ->toArray();

                $bookmarkedPostIds = DB::table('post_bookmarks')
                    ->where('is_bookmarked', true)
                    ->where('user_id', $authUserId)
                    ->pluck('post_id')
                    ->toArray();

                // Transform each post in the collection
                $posts->getCollection()->transform(function ($post) use ($likedPostIds, $bookmarkedPostIds) {
                    if (!($post instanceof \App\Models\Post)) {
                        throw new \Exception('Expected Post instance but got ' . get_class($post));
                    }

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
            $request->except(['media', 'music']) + ['user_id' => _user()->id]
        );

        ini_set('upload_max_filesize', '100M');
        ini_set('post_max_size', '100M');
        ini_set('memory_limit', '256M');

        if ($request->hasFile('media')) {
            $post->addMedia($request->file('media'))->toMediaCollection('posts');
        }

        if ($request->hasFile('music')) {
            $post->addMedia($request->file('music'))->toMediaCollection('music');
        }

        dispatch(new NewPostNotificationJob($post));

        return new PostResource($post);
    }

    public function store2(StorePostRequest $request)
    {
        $post = Post::create(
            $request->except(['media', 'chunk', 'chunkIndex', 'totalChunks']) + ['user_id' => _user()->id]
        );

        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $chunkIndex = $request->input('chunkIndex');
            $totalChunks = $request->input('totalChunks');

            // Define temp storage path
            $tempPath = storage_path('app/uploads/' . $post->id);
            $chunkPath = $tempPath . '/' . $chunkIndex;

            // Create directory if not exists
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0755, true);
            }

            // Move chunk to temp path
            $file->move($tempPath, $chunkIndex);

            // Check if all chunks are uploaded
            if ($this->allChunksUploaded($tempPath, $totalChunks)) {
                $finalPath = $tempPath . '/final.mp4';

                // Merge chunks
                $this->mergeChunks($tempPath, $finalPath);

                // Add to media collection
                $post->addMedia($finalPath)->toMediaCollection('posts');

                // Cleanup temporary files
                File::deleteDirectory($tempPath);
            }
        }

        return new PostResource($post);
    }

    // Helper to check if all chunks are uploaded
    private function allChunksUploaded($tempPath, $totalChunks)
    {
        $files = File::files($tempPath);
        return count($files) == $totalChunks;
    }

    // Helper to merge chunks
    private function mergeChunks($tempPath, $finalPath)
    {
        $chunks = File::files($tempPath);
        sort($chunks);

        $final = fopen($finalPath, 'ab');

        foreach ($chunks as $chunk) {
            fwrite($final, file_get_contents($chunk->getRealPath()));
        }

        fclose($final);
    }

    public function show(Post $post)
    {
        // Load the relationships that you need for the post (media, likes, comments, and user)
        $post->load('media', 'likes', 'comments.user', 'comments.replies.user', 'user', 'comments.likes', 'comments.replies.likes');

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




    public function getReels()
    {
        $posts = Post::with([
            'media',
            'likes',
            'comments.user',
            'comments.likes',
            'comments.replies.user',
            'comments.replies.likes',
            'user'
        ])
        ->where('type', 'reel')
        ->where('is_story', false)
        ->orderBy('created_at', 'desc')
        ->paginate(20);

        if (auth()->check()) {
            $authUserId = auth()->id();

            // Get liked and bookmarked post IDs
            $likedPostIds = DB::table('post_likes')
                ->where('is_liked', true)
                ->where('user_id', $authUserId)
                ->pluck('post_id')
                ->toArray();

            $bookmarkedPostIds = DB::table('post_bookmarks')
                ->where('is_bookmarked', true)
                ->where('user_id', $authUserId)
                ->pluck('post_id')
                ->toArray();

            // Transform each post in the collection
            $posts->getCollection()->transform(function ($post) use ($likedPostIds, $bookmarkedPostIds) {
                if (!($post instanceof \App\Models\Post)) {
                    throw new \Exception('Expected Post instance but got ' . get_class($post));
                }

                $post->liked_by_auth_user = in_array($post->id, $likedPostIds);
                $post->bookmarked_by_auth_user = in_array($post->id, $bookmarkedPostIds);

                return $post;
            });
        }

        // Return paginated posts wrapped in PostResource
        return PostResource::collection($posts);
    }


    public function userReels(User $user)
    {
        $posts = Post::with([
            'media',
            'likes',
            'comments.user',
            'comments.likes',
            'comments.replies.user',
            'comments.replies.likes',
            'user'
        ])
            ->where('user_id', $user->id)
            ->where('type', 'reel')
            ->where('is_story', false)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

            if (auth()->check()) {
                $authUserId = auth()->id();

                // Get liked and bookmarked post IDs
                $likedPostIds = DB::table('post_likes')
                    ->where('is_liked', true)
                    ->where('user_id', $authUserId)
                    ->pluck('post_id')
                    ->toArray();

                $bookmarkedPostIds = DB::table('post_bookmarks')
                    ->where('is_bookmarked', true)
                    ->where('user_id', $authUserId)
                    ->pluck('post_id')
                    ->toArray();

                // Transform each post in the collection
                $posts->getCollection()->transform(function ($post) use ($likedPostIds, $bookmarkedPostIds) {
                    if (!($post instanceof \App\Models\Post)) {
                        throw new \Exception('Expected Post instance but got ' . get_class($post));
                    }

                    $post->liked_by_auth_user = in_array($post->id, $likedPostIds);
                    $post->bookmarked_by_auth_user = in_array($post->id, $bookmarkedPostIds);

                    return $post;
                });
            }

        return PostResource::collection($posts);
    }



    public function getStories()
    {
        $posts = Post::with([
            'media',
            'likes',
            'comments.user',
            'comments.likes',
            'comments.replies.user',
            'comments.replies.likes',
            'user'
        ])
        ->where('is_story', true)
        ->where('type', '!=', 'reel')
        ->where('status', 'active')
        ->orderBy('created_at', 'desc')
        ->paginate(20);

        if (auth()->check()) {
            $authUserId = auth()->id();

            // Get liked and bookmarked post IDs
            $likedPostIds = DB::table('post_likes')
                ->where('is_liked', true)
                ->where('user_id', $authUserId)
                ->pluck('post_id')
                ->toArray();

            $bookmarkedPostIds = DB::table('post_bookmarks')
                ->where('is_bookmarked', true)
                ->where('user_id', $authUserId)
                ->pluck('post_id')
                ->toArray();

            // Transform each post in the collection
            $posts->getCollection()->transform(function ($post) use ($likedPostIds, $bookmarkedPostIds) {
                if (!($post instanceof \App\Models\Post)) {
                    throw new \Exception('Expected Post instance but got ' . get_class($post));
                }

                $post->liked_by_auth_user = in_array($post->id, $likedPostIds);
                $post->bookmarked_by_auth_user = in_array($post->id, $bookmarkedPostIds);

                return $post;
            });
        }

        // Return paginated posts wrapped in PostResource
        return PostResource::collection($posts);
    }

    public function getStoriesUpdated()
    {
        // Get all users with their stories
        $usersWithStories = \App\Models\User::with([
            'posts' => function ($query) {
                $query->where('is_story', true)
                    ->where('type', '!=', 'reel')
                    ->where('status', 'active')
                    ->orderBy('created_at', 'desc') // Order posts by created_at descending
                    ->with('media', 'likes', 'comments.user', 'comments.replies.user', 'comments.likes', 'comments.replies.likes');
            }
        ])->whereHas('posts', function ($query) {
            $query->where('is_story', true)
                ->where('type', '!=', 'reel');
        })->paginate(20);


        // If the user is authenticated, check likes and bookmarks
        if (auth()->check()) {
            $authUserId = auth()->id();

            $likedPostIds = DB::table('post_likes')
                ->where('is_liked', true)
                ->where('user_id', $authUserId)
                ->pluck('post_id')
                ->toArray();

            $bookmarkedPostIds = DB::table('post_bookmarks')
                ->where('is_bookmarked', true)
                ->where('user_id', $authUserId)
                ->pluck('post_id')
                ->toArray();

            // Iterate through users and their stories
            $usersWithStories->getCollection()->transform(function ($user) use ($likedPostIds, $bookmarkedPostIds) {
                $user->posts->transform(function ($post) use ($likedPostIds, $bookmarkedPostIds) {
                    $post->liked_by_auth_user = in_array($post->id, $likedPostIds);
                    $post->bookmarked_by_auth_user = in_array($post->id, $bookmarkedPostIds);
                    return $post;
                });
                return $user;
            });
        }

        // Return users and their stories
        return UserStoryResource::collection($usersWithStories);
    }


    public function getSpotlightStories()
    {
        // Get the user with the highest number of followers who also has stories
        $topUser = User::withCount('followers') // Count followers for each user
            ->whereHas('posts', function ($query) {
                $query->where('is_story', true)
                    ->where('type', '!=', 'reel')
                    ->where('status', 'active');
            })
            ->orderByDesc('followers_count') // Order by most followers
            ->first(); // Get only one user

        if (!$topUser) {
            return response()->json(['message' => 'No stories available'], 404);
        }

        // Load stories for the top user
        $topUser->load([
            'posts' => function ($query) {
                $query->where('is_story', true)
                    ->where('type', '!=', 'reel')
                    ->where('status', 'active')
                    ->orderBy('created_at', 'desc')
                    ->with('media', 'likes', 'comments.user', 'comments.replies.user', 'comments.likes', 'comments.replies.likes');
            }
        ]);

        // If the user is authenticated, check likes and bookmarks
        if (auth()->check()) {
            $authUserId = auth()->id();

            $likedPostIds = DB::table('post_likes')
                ->where('is_liked', true)
                ->where('user_id', $authUserId)
                ->pluck('post_id')
                ->toArray();

            $bookmarkedPostIds = DB::table('post_bookmarks')
                ->where('is_bookmarked', true)
                ->where('user_id', $authUserId)
                ->pluck('post_id')
                ->toArray();

            // Add liked and bookmarked status
            $topUser->posts->transform(function ($post) use ($likedPostIds, $bookmarkedPostIds) {
                $post->liked_by_auth_user = in_array($post->id, $likedPostIds);
                $post->bookmarked_by_auth_user = in_array($post->id, $bookmarkedPostIds);
                return $post;
            });
        }

        // Return only the top user's stories
        return new UserStoryResource($topUser);
    }




    public function userStories(User $user)
    {
        $posts = Post::with('media', 'likes', 'comments.user', 'comments.replies.user', 'user', 'comments.likes', 'comments.replies.likes' )
            ->where('user_id', $user->id)
            ->where('is_story', true)
            ->where('type', '!=', 'reel')
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

            if (auth()->check()) {
                $authUserId = auth()->id();

                // Get liked and bookmarked post IDs
                $likedPostIds = DB::table('post_likes')
                    ->where('is_liked', true)
                    ->where('user_id', $authUserId)
                    ->pluck('post_id')
                    ->toArray();

                $bookmarkedPostIds = DB::table('post_bookmarks')
                    ->where('is_bookmarked', true)
                    ->where('user_id', $authUserId)
                    ->pluck('post_id')
                    ->toArray();

                // Transform each post in the collection
                $posts->getCollection()->transform(function ($post) use ($likedPostIds, $bookmarkedPostIds) {
                    if (!($post instanceof \App\Models\Post)) {
                        throw new \Exception('Expected Post instance but got ' . get_class($post));
                    }

                    $post->liked_by_auth_user = in_array($post->id, $likedPostIds);
                    $post->bookmarked_by_auth_user = in_array($post->id, $bookmarkedPostIds);

                    return $post;
                });
            }

        return PostResource::collection($posts);
    }


    public function getFollowingPosts()
    {
        $authUserId = auth()->id();

        // Get the IDs of users the auth user follows
        $followingIds = DB::table('follows')
            ->where('follower_id', $authUserId)
            ->pluck('following_id');

        // Fetch posts from followed users
        $posts = Post::with('media', 'likes', 'comments.user', 'comments.replies.user', 'user' , 'comments.likes', 'comments.replies.likes')
            ->whereIn('user_id', $followingIds)
            ->where('is_story', false)
            ->where('type', '!=', 'reel')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        if (auth()->check()) {
            // Get liked and bookmarked post IDs
            $likedPostIds = DB::table('post_likes')
                ->where('is_liked', true)
                ->where('user_id', $authUserId)
                ->pluck('post_id')
                ->toArray();

            $bookmarkedPostIds = DB::table('post_bookmarks')
                ->where('is_bookmarked', true)
                ->where('user_id', $authUserId)
                ->pluck('post_id')
                ->toArray();

            // Transform each post in the collection
            $posts->getCollection()->transform(function ($post) use ($likedPostIds, $bookmarkedPostIds) {
                if (!($post instanceof \App\Models\Post)) {
                    throw new \Exception('Expected Post instance but got ' . get_class($post));
                }

                $post->liked_by_auth_user = in_array($post->id, $likedPostIds);
                $post->bookmarked_by_auth_user = in_array($post->id, $bookmarkedPostIds);

                return $post;
            });
        }

        return PostResource::collection($posts);
    }



    public function getBookmarkedPosts(User $user)
    {

        // Get the IDs of users the auth user follows
        $bookmarkedIds = DB::table('post_bookmarks')
            ->where('user_id', $user->id)
            ->pluck('post_id');

        // Fetch posts from followed users
        $posts = Post::with('media', 'likes', 'comments.user', 'comments.replies.user', 'user' , 'comments.likes', 'comments.replies.likes')
            ->whereIn('id', $bookmarkedIds)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        if (auth()->check()) {
            $authUserId = auth()->id();
            // Get liked and bookmarked post IDs
            $likedPostIds = DB::table('post_likes')
                ->where('is_liked', true)
                ->where('user_id', $authUserId)
                ->pluck('post_id')
                ->toArray();

            $bookmarkedPostIds = DB::table('post_bookmarks')
                ->where('is_bookmarked', true)
                ->where('user_id', $authUserId)
                ->pluck('post_id')
                ->toArray();

            // Transform each post in the collection
            $posts->getCollection()->transform(function ($post) use ($likedPostIds, $bookmarkedPostIds) {
                if (!($post instanceof \App\Models\Post)) {
                    throw new \Exception('Expected Post instance but got ' . get_class($post));
                }

                $post->liked_by_auth_user = in_array($post->id, $likedPostIds);
                $post->bookmarked_by_auth_user = in_array($post->id, $bookmarkedPostIds);

                return $post;
            });
        }

        return PostResource::collection($posts);
    }

    public function toggleCommenting(Post $post)
    {
        // Check if the authenticated user is the owner of the post (optional)
        if (auth()->id() !== $post->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to toggle commenting for this post.',
            ], 403);
        }

        // Toggle commenting_enabled
        $post->commenting_enabled = !$post->commenting_enabled;
        $post->save();

        return response()->json([
            'success' => true,
            'commenting_enabled' => $post->commenting_enabled,
            'message' => 'Commenting has been ' . ($post->commenting_enabled ? 'enabled' : 'disabled') . '.',
        ]);
    }






}
