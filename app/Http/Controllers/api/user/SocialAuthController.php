<?php

namespace App\Http\Controllers\api\user;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        $user = Socialite::driver('google')->stateless()->user();
        return $this->findOrCreateUser($user, 'google');
    }

    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->stateless()->redirect();
    }

    public function handleFacebookCallback()
    {
        $user = Socialite::driver('facebook')->stateless()->user();
        return $this->findOrCreateUser($user, 'facebook');
    }

    private function findOrCreateUser($socialUser, $provider)
    {
        $user = User::where('provider_id', $socialUser->getId())
            ->where('provider', $provider)
            ->first();

        if (!$user) {
            $username = $this->generateUniqueUsername($socialUser->getName());

            $user = User::create([
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'password' => bcrypt(Str::random(8)),
                'username' => $username,
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
            ]);

            $user->email_verified_at = now();
            $user->save();
        }

        // Generate token or log in user
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Generate a unique username based on the user's name.
     */
    private function generateUniqueUsername($name)
    {
        $baseUsername = Str::slug($name);
        $username = $baseUsername;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . '-' . $counter;
            $counter++;
        }

        return $username;
    }


    public function googleStore(Request $request)
    {
        $user = User::where('provider_id', $request->provider_id)
            ->where('provider', $request->provider)
            ->first();

        if (!$user) {
            $username = $this->generateUniqueUsername($request->name);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt(Str::random(8)),
                'username' => $username,
                'provider' => $request->provider,
                'provider_id' => $request->provider_id,
            ]);

            $user->email_verified_at = now();
            $user->save();
        }

        // Generate token or log in user
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }


}
