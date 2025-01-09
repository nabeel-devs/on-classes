<?php

namespace App\Http\Controllers\api\user;

use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreOrderRequest;
use Illuminate\Support\Facades\Validator;
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

            Transaction::create([
                'user_id' => $productOwner->id,
                'amount' => $totalPrice,
                'status' => 'success',
                'description' => 'Product purchase',
                'type' => 'credit',
            ]);

            $user->bought_products()->attach($product->id, [
                'quantity' => $item['quantity'],
                'purchase_price' => $item['price'],
                'order_id' => $order->id,
            ]);
        }

        Transaction::create([
            'user_id' => $user->id,
            'amount' => $request->total,
            'status' => 'success',
            'description' => 'Products purchase',
            'type' => 'debit',
        ]);

        return response()->json($order->load('items'), 201);
    }






    public function verifyDiscountCode(Request $request)
    {
        // Validate the incoming request for discount code
        $validator = Validator::make($request->all(), [
            'discount_code' => 'required|string|max:50',
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Retrieve the product by its ID
        $product = Product::find($request->product_id);

        // Check if the product has a discount code
        if ($product->discount_code && $product->discount_code === $request->discount_code) {
            // Check if the product is discounted and the discount code is still valid
            if ($product->is_discounted) {
                return response()->json([
                    'success' => true,
                    'discount_percentage' => $product->discount_percentage,
                    'message' => 'Discount code is valid.',
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'The discount code is not applicable for this product.',
            ], 400);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid discount code for this product.',
        ], 400);
    }

    public function userProducts(Request $request)
    {
        $user = auth()->user();

        // Retrieve the user's products along with the pivot data (quantity, purchase_price, order_id)
        $products = $user->bought_products()
                        ->withPivot('quantity', 'purchase_price', 'order_id')
                        ->get();

        return response()->json($products);
    }




}
