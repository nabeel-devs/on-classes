<?php

namespace App\Http\Controllers\api\user;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreOrderRequest;
use Laravel\Cashier\Exceptions\IncompletePayment;

class ProductOrderController extends Controller
{
    public function store(StoreOrderRequest $request)
    {
        $user = auth()->user();

        if (!$user->hasStripeId()) {
            $user->createAsStripeCustomer();
        }

        if (!$user->hasPaymentMethod()) {
            try {
                $paymentMethod = $user->addPaymentMethod($request->payment_method);
                $user->updateDefaultPaymentMethod($paymentMethod->id);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Failed to add payment method: ' . $e->getMessage()], 400);
            }
        }

        try {
            // Ensure only the necessary options are provided
            $options = [ 'return_url' => 'https://example.com/checkout' ];
            $user->charge($request->total * 100, $user->defaultPaymentMethod()->id, $options);
        } catch (IncompletePayment $e) {
            return response()->json(['error' => 'Payment failed: ' . $e->getMessage()], 500);
        }

        $order = Order::create($request->only('user_id', 'total', 'fee', 'status'));

        foreach ($request->items as $item) {

            $product = Product::findOrFail($item['product_id']);

            $totalPrice = $item['quantity'] * $item['price'];

            $productOwner = $product->user;
            $productOwner->wallet_amount += $totalPrice;
            $productOwner->save();

            $order->items()->create($item);
        }

        return response()->json($order->load('items'), 201);
    }



}
