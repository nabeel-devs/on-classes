<?php

namespace App\Http\Controllers\api\creator;

use Stripe\Stripe;
use Stripe\Transfer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    public function transferToVisa(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'card_number' => 'required|string',
            'expiry_month' => 'required|integer|min:1|max:12',
            'expiry_year' => 'required|integer',
            'cvv' => 'required|string|max:4',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = auth()->user();

        if ($user->wallet_amount < $request->amount) {
            return response()->json(['error' => 'Insufficient wallet balance'], 400);
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            // Create a Stripe payout to a card
            $transfer = Transfer::create([
                'amount' => $request->amount * 100, // Stripe uses cents
                'currency' => 'usd',
                'destination' => $request->card_number,
            ]);

            // Deduct amount from wallet
            $user->wallet_balance -= $request->amount;
            $user->save();

            return response()->json(['message' => 'Transfer successful', 'transfer' => $transfer], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to transfer: ' . $e->getMessage()], 500);
        }
    }
}
