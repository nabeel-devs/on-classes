<?php

namespace App\Http\Controllers\api\user;

use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FollowController extends Controller
{
    public function follow(User $user)
    {
        $currentUser = auth()->user();

        // Prevent a user from following themselves
        if ($currentUser->id === $user->id) {
            return response()->json(['message' => 'You cannot follow yourself'], 400);
        }

        // Check if the follow record already exists
        $exists = Follow::where('follower_id', $currentUser->id)
            ->where('following_id', $user->id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'You are already following this user'], 400);
        }

        // Create a new follow record
        Follow::create([
            'follower_id' => $currentUser->id,
            'following_id' => $user->id,
        ]);

        return response()->json(['message' => "You are now following {$user->name}."]);
    }


    public function unfollow(User $user)
    {
        $currentUser = auth()->user();

        // Check if the follow record exists
        $follow = Follow::where('follower_id', $currentUser->id)
            ->where('following_id', $user->id)
            ->first();

        if (!$follow) {
            return response()->json(['message' => 'You are not following this user'], 400);
        }

        // Delete the follow record
        $follow->delete();

        return response()->json(['message' => "You have unfollowed {$user->name}."]);
    }


    public function getFollowers()
    {
        $followers = auth()->user()->followers()->with('follower')->get();

        return response()->json($followers);
    }

    public function getFollowings()
    {
        $followings = auth()->user()->followings()->with('following')->get();

        return response()->json($followings);
    }

    public function getFollowCounts(Request $request, User $user = null)
    {
        $user = $user ?? auth()->user(); // Use the authenticated user if no user is specified.

        $followersCount = $user->followers()->count();
        $followingsCount = $user->followings()->count();

        return response()->json([
            'followers_count' => $followersCount,
            'followings_count' => $followingsCount,
        ]);
    }






}
