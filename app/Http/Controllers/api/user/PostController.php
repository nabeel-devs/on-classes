<?php

namespace App\Http\Controllers\api\user;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\post\PostResource;
use App\Http\Requests\user\StorePostRequest;
use App\Http\Requests\user\UpdatePostRequest;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('media')->paginate();
        return PostResource::collection($posts);
    }

    public function store(StorePostRequest $request)
    {
        $post = Post::create($request->validated() + ['user_id' => _user()->id]);

        if ($request->hasFile('media')) {
            $post->addMedia($request->file('media'))->toMediaCollection('posts');
        }

        return new PostResource($post);
    }

    public function show(Post $post)
    {
        return new PostResource($post->load('media'));
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        $post->update($request->validated());

        if ($request->hasFile('media')) {
            $post->clearMediaCollection('posts');
            $post->addMedia($request->file('media'))->toMediaCollection('posts');
        }

        return new PostResource($post);
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return response()->noContent();
    }
}
