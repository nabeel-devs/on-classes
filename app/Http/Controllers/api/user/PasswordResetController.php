<?php

namespace App\Http\Controllers\api\user;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Jobs\user\ResetPasswordJob;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PasswordResetController extends Controller
{
    public function forgotPassword(Request $request)
    {

        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return jsonResponse(false, null, 'User not found.', 404);
        }

       // Generate a 4-digit reset code
       $resetCode = rand(1000, 9999);

       // Save the reset code in the password_reset_tokens table
       DB::table('password_reset_tokens')->updateOrInsert(
           ['email' => $user->email],
           ['email' => $user->email, 'token' => $resetCode]
       );

    //    Mail::to($user->email)->send(new ResetPasswordEmail($user, $resetCode));
        ResetPasswordJob::dispatch($user, $resetCode);

        return response()->json([
            'message' => 'Password reset code sent to your email',
            'reset_code' => $resetCode,
        ]);
    }

    // Reset Password with OTP
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $reset = DB::table('password_reset_tokens')
                    ->where('email', $request->email)
                    ->where('token', $request->token)
                    ->first();

        if (!$reset) {
            return jsonResponse(false, null, 'Invalid or expired OTP.', 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return jsonResponse(false, null, 'User Not Found', 404);

        }

        $user->update(['password' => Hash::make($request->password)]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return jsonResponse(true, null, 'Password reset successfully.');
    }
}
