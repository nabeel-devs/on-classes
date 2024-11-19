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
        $validated['password'] = Hash::make($validated['password']);

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
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return jsonResponse(false, null, 'Invalid credentials', 401);
        }

        $user = new UserResource($user);
        $token = $user->createToken('user_auth_token')->plainTextToken;

        return jsonResponse(
            true,
            ['user' => $user, 'token' => $token],
            'Login successful'
        );
    }

}
