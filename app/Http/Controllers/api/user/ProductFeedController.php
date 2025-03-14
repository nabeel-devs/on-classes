<?php

namespace App\Http\Controllers\api\user;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
        // Fetch all products and count reviews
        $products = Product::with(['user', 'category', 'media', 'reviews.user'])
            ->withCount('reviews') // Count the number of reviews
            ->orderBy('reviews_count', 'desc') // Order by review count
            ->get()
            ->filter(function ($product) {
                return $product->reviews_count > 1; // Filter for products with more than 1 review
            });

        return ProductResource::collection($products);
    }


    public function categoryProducts($categoryId)
    {
        $products = Product::with(['user', 'category', 'media', 'reviews.user'])
            ->where('category_id', $categoryId)->get();

        return ProductResource::collection($products);
    }
}
