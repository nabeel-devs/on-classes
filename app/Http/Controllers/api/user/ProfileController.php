<?php

namespace App\Http\Controllers\api\user;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\user\UserResource;
use App\Http\Requests\user\UpdateUserRequest;
use App\Http\Resources\user\UserProfileResource;

class ProfileController extends Controller
{
    public function uploadDp(Request $request)
    {
        $request->validate([
            'dp' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = auth()->user();

        // Remove the old DP if exists
        $user->clearMediaCollection('dp');

        // Add the new DP
        $user->addMediaFromRequest('dp')->toMediaCollection('dp');

        return response()->json([
            'message' => 'Display picture updated successfully.',
            'dp_url' => $user->getDpUrl('thumb'),
        ]);
    }

    public function getDp()
    {
        $user = auth()->user();

        return response()->json([
            'dp_url' => $user->getDpUrl(),
        ]);
    }


    public function update(UpdateUserRequest $request)
    {
        $user = auth()->user(); // Get the authenticated user

        // Update the user's information
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->username = $request->input('username');
        $user->email = $request->input('email');
        $user->dob = $request->input('dob');
        $user->gender = $request->input('gender');
        $user->phone = $request->input('phone');
        $user->bio = $request->input('bio');

        // If the user is updating their password
        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        // Handle profile picture upload (if provided)
        if ($request->hasFile('dp')) {
            $user->addMediaFromRequest('dp')->toMediaCollection('dp');
        }

        // Save the updated user data
        $user->save();

        return response()->json([
            'message' => 'User information updated successfully.',
            'user' => new UserResource($user),
        ]);
    }

    public function show()
    {
        $user = auth()->user();

        // Load the relationships
        $user->load('links', 'posts.media'); // Load both links and posts (which may include images and videos)

        return new UserProfileResource($user);
    }

    public function showUserProfile(User $user)
    {

        $user->load('links', 'posts.media'); // Load both links and posts (which may include images and videos)

        return new UserProfileResource($user);
    }


public function updateOnlineStatus(Request $request)
{
    $request->validate([
        'online' => 'required|boolean',
    ]);

    $user = _user();  // Get the authenticated user
    $user->online = $request->online;
    $user->save();

    return response()->json([
        'message' => 'Online status updated successfully',
        'online' => $user->online,
    ]);
}



    public function updateRole(Request $request)
    {

        $request->validate([
            'role' => 'required|in:member,creator',
        ]);
        $user = auth()->user(); // Get the authenticated user


        $user->role = $request->input('role');

        $user->save();

        return response()->json([
            'message' => 'User role updated successfully.',
            'user' => new UserResource($user),
        ]);
    }






}
