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

    public function popular()
    {
        // Fetch products with more than one review, order by the count of reviews
        $products = Product::with(['user', 'category', 'media', 'reviews.user'])
            ->withCount('reviews') // Count the number of reviews
            ->having('reviews_count', '>', 1) // Filter for products with more than 1 review
            ->orderBy('reviews_count', 'desc') // Order by review count, descending
            ->get();

        return ProductResource::collection($products);
    }

    public function categoryProducts($categoryId)
    {
        $products = Product::with(['user', 'category', 'media', 'reviews.user'])
            ->where('category_id', $categoryId)->get();

        return ProductResource::collection($products);
    }
}
