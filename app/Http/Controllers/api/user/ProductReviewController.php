<?php

namespace App\Http\Controllers\api\user;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\ProductReview;
use App\Http\Controllers\Controller;
use App\Http\Resources\user\ProductReviewResource;

class ProductReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|between:1,5',
            'review' => 'nullable|string|max:500',
        ]);

        $review = new ProductReview();
        $review->product_id = $request->product_id;
        $review->user_id = _user()->id;
        $review->rating = $request->rating;
        $review->review = $request->review;
        $review->save();

        return new ProductReviewResource($review);
    }

    public function index(Product $product)
    {
        $reviews = $product->reviews()->with('user')->get();

        return ProductReviewResource::collection($reviews);
    }

    public function update(Request $request, ProductReview $review)
    {
        $request->validate([
            'rating' => 'required|integer|between:1,5',
            'review' => 'nullable|string|max:500',
        ]);

        // Check if the authenticated user is the one who created the review
        if ($review->user_id !== _user()->id) {
            return response()->json(['message' => 'You are not authorized to update this review'], 403);
        }

        $review->rating = $request->rating;
        $review->review = $request->review;
        $review->save();

        return new ProductReviewResource($review);
    }


    public function destroy(ProductReview $review)
    {

        if ($review->user_id !== _user()->id) {
            return response()->json(['message' => 'You are not authorized to delete this review'], 403);
        }

        $review->delete();

        return response()->json([
            'message' => 'Review deleted successfully'
        ]);
    }




}
