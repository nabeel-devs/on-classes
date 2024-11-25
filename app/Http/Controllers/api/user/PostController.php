<?php

namespace App\Http\Controllers\api\user;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\post\PostResource;
use App\Http\Requests\user\StorePostRequest;
use App\Http\Requests\user\UpdatePostRequest;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('media', 'likes', 'comments')->paginate(20);
        return PostResource::collection($posts);
    }

    public function userPosts(User $user)
    {
        $posts = Post::with('media', 'likes', 'comments')
            ->where('user_id', $user->id)->paginate(20);
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
        return new PostResource($post->load('media', 'likes', 'comments'));
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
