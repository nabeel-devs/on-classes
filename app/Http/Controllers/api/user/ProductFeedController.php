<?php

namespace App\Http\Controllers\api\user;

use App\Models\Post;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\post\PostResource;
use App\Http\Resources\creator\ProductResource;

class ProductFeedController extends Controller
{
    public function index()
    {
        $products = Product::with(['user', 'category', 'media', 'reviews.user'])->get();
        return ProductResource::collection($products);
    }

    public function show(Product $product)
    {
        $user = auth()->user();

        // Check if the authenticated user has bought this product
        $hasBought = $user ? $user->bought_products()->where('product_id', $product->id)->exists() : false;

        return (new ProductResource($product->load(['user', 'category', 'media', 'reviews.user'])))
                    ->additional(['has_bought' => $hasBought]);
    }


    public function popular()
    {
        // Get popular products
        $products = Product::with(['user', 'category', 'media', 'reviews.user'])
            ->withCount('reviews')
            ->orderBy('reviews_count', 'desc')
            ->get()
            ->filter(function ($product) {
                return $product->reviews_count > 1;
            });

        // Get popular reels/posts
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
        // ->orderBy('likes_count', 'desc')
        ->paginate(20); // Use pagination for posts

        // Check if user is authenticated and add like/bookmark flags
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

            $posts->getCollection()->transform(function ($post) use ($likedPostIds, $bookmarkedPostIds) {
                $post->liked_by_auth_user = in_array($post->id, $likedPostIds);
                $post->bookmarked_by_auth_user = in_array($post->id, $bookmarkedPostIds);
                return $post;
            });
        }

        // Return both in one response
        return response()->json([
            'success' => true,
            'popular_products' => ProductResource::collection($products),
            'popular_reels' => PostResource::collection($posts),
            'pagination' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ]
        ]);
    }



    public function categoryProducts($categoryId)
    {
        $products = Product::with(['user', 'category', 'media', 'reviews.user'])
            ->where('category_id', $categoryId)->get();

        return ProductResource::collection($products);
    }
}
