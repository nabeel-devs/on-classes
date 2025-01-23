<?php

namespace App\Http\Controllers\api\user;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\user\LoginRequest;
use App\Http\Resources\user\UserResource;
use App\Http\Requests\user\RegisterRequest;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();
        // $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        // Send the email verification code
        $verificationCode = $user->sendEmailVerificationNotification();

        $userResource = new UserResource($user);
        $token = $user->createToken('user_auth_token')->plainTextToken;

        return jsonResponse(
            true,
            [
                'user' => $userResource,
                'token' => $token,
                'verification_code' => $verificationCode // Include the verification code in the response
            ],
            'User registered successfully. Please check your email for the verification code.',
            Response::HTTP_CREATED
        );
    }


    public function login(LoginRequest $request)
    {
        // Check if the user exists by email or username
        $user = User::where('email', $request->email)
                    ->orWhere('username', $request->email) // Check for username
                    ->first();

        // Validate user existence and password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return jsonResponse(false, null, 'Invalid credentials', 401);
        }

        // Transform the user data and create a token
        $user = new UserResource($user);
        $token = $user->createToken('user_auth_token')->plainTextToken;

        // Return the response
        return jsonResponse(
            true,
            ['user' => $user, 'token' => $token],
            'Login successful'
        );
    }


    public function createPassword(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Find the user by email
        $user = auth()->user();

        // Check if the user is verified
        if (!$user->hasVerifiedEmail()) {
            return jsonResponse(false, null, 'User email is not verified.', 403);
        }

        // Update the user's password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return jsonResponse(
            true,
            null,
            'Password created successfully.'
        );
    }


}
