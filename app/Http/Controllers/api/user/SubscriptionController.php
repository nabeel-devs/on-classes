<?php

namespace App\Http\Controllers\api\user;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Laravel\Cashier\Exceptions\IncompletePayment;

class SubscriptionController extends Controller
{
    public function subscribe(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'payment_method' => 'required|string',
            'plan_id' => 'required|string',
        ]);

        $user = $request->user();
        $paymentMethod = $request->input('payment_method');
        $planId = $request->input('plan_id');

        try {
            $user->newSubscription('default', $planId)->create($paymentMethod);

            return response()->json([
                'success' => true,
                'message' => 'Subscription created successfully.',
            ], 201);
        } catch (IncompletePayment $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription requires additional verification.',
                'payment_url' => route('cashier.payment', [$exception->payment->id, 'redirect' => route('home')])
            ], 402);
        }
    }

    public function updatePaymentMethod(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string',
        ]);

        $user = $request->user();
        $paymentMethod = $request->input('payment_method');

        $user->updateDefaultPaymentMethod($paymentMethod);

        return response()->json([
            'success' => true,
            'message' => 'Payment method updated successfully.',
        ]);
    }

    public function status(Request $request)
    {
        $user = $request->user();

        if ($user->subscribed('default')) {
            return response()->json([
                'success' => true,
                'message' => 'User is subscribed.',
                'subscription_status' => $user->subscription('default')->stripe_status,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'User is not subscribed.',
        ]);
    }


    public function getPlans()
    {
        $plans = [
            'monthly' => [
                'id' => config('services.stripe.monthly.id'),
                'price' => config('services.stripe.monthly.price'),
            ],
            'yearly' => [
                'id' => config('services.stripe.yearly.id'),
                'price' => config('services.stripe.yearly.price'),
            ],
        ];

        return response()->json([
            'success' => true,
            'plans' => $plans,
        ], 200);
    }



}
