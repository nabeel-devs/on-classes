<?php

namespace App\Http\Controllers\api\user;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\user\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;

class EmailVerificationController extends Controller
{
    public function verify(EmailVerificationRequest $request)
    {
        $user = _user();

        if ($user->verification_code != $request->verification_code) {
            return jsonResponse(false, null, 'Invalid verification code.', 400);
        }

        if (Carbon::parse($user->verification_code_expires_at)->isPast()) {
            return jsonResponse(false, null, 'Verification code has expired.', 400);
        }

        $user->markEmailAsVerified();

        // Clear the verification code
        $user->verification_code = null;
        $user->verification_code_expires_at = null;
        $user->save();

        return jsonResponse(true, null, 'Email verified successfully.');
    }

    public function resend()
    {
        $user = _user();

        if ($user->hasVerifiedEmail()) {
            return jsonResponse(true, null, 'Your email is already verified.');
        }

        $user->sendEmailVerificationNotification();

        return jsonResponse(true, null, 'Verification code resent.');
    }

}
