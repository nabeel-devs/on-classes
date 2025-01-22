<?php

namespace App\Http\Controllers\api\user;

use App\Models\User;
use App\Models\Course;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\user\UserResource;

class UserController extends Controller
{
    public function allCreators()
    {
        $allCreators = User::where('role', 'creator')
            ->orderBy('created_at', 'desc')
            ->get();

        return UserResource::collection($allCreators);
    }

    public function topCreators()
    {
        $allCreators = User::with('products')
            ->where('role', 'creator')
            ->withCount('products') // Count the number of products
            ->orderBy('products_count', 'desc')
            ->get()
            ->filter(function ($creator) {
                return $creator->products_count > 1; // Filter creators with more than 1 product
            });

        return UserResource::collection($allCreators);
    }

    public function creatorInfo($userId)
    {
        $user = User::findOrFail($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        // Count courses created by the user
        $courseCount = Course::where('user_id', $userId)->count();

        // Count distinct users who bought products of the user
        $userCount = Product::where('user_id', $userId)
            ->with('users') // Load the relationship
            ->get()
            ->pluck('users') // Get the users collection
            ->flatten() // Flatten the nested collections
            ->unique('id') // Get unique users by their ID
            ->count();

        $totalCredits = Transaction::where('user_id', $userId)
            ->where('type', 'credit') // Filter only credit transactions
            ->sum('amount'); // Sum the amounts

        $currentBalance = $user->wallet_amount;
        // Prepare the response
        return response()->json([
            'user_id' => $userId,
            'course_count' => $courseCount,
            'student_count' => $userCount,
            'total_credits' => $totalCredits,
            'current_balance' => $currentBalance
        ]);
    }



    public function destroy(User $user)
    {
        $user->delete();
        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.',
        ], 200);
    }




}
