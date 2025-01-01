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

    public function categoryProducts($categoryId)
    {
        $products = Product::with(['user', 'category', 'media', 'reviews.user'])
            ->where('category_id', $categoryId)->get();

        return ProductResource::collection($products);
    }
}
