<?php

namespace App\Http\Controllers\api\user;

use App\Models\Course;
use App\Models\CourseOrder;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreCourseOrderRequest;
use Laravel\Cashier\Exceptions\IncompletePayment;

class CourseOrderController extends Controller
{
    public function store(StoreCourseOrderRequest $request)
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

        $order = CourseOrder::create($request->only('user_id', 'total', 'fee', 'status'));

        foreach ($request->items as $item) {

            $course = Course::findOrFail($item['course_id']);

            $totalPrice = $item['quantity'] * $item['price'];

            $courseOwner = $course->user;
            $courseOwner->wallet_amount += $totalPrice;
            $courseOwner->save();

            $order->items()->create($item);

            Transaction::create([
                'user_id' => $courseOwner->id,
                'amount' => $totalPrice,
                'status' => 'success',
                'description' => 'Course purchase',
                'type' => 'credit',
            ]);

            // $user->bought_products()->attach($course->id, [
            //     'quantity' => $item['quantity'],
            //     'purchase_price' => $item['price'],
            //     'order_id' => $order->id,
            // ]);
        }

        Transaction::create([
            'user_id' => $user->id,
            'amount' => $request->total,
            'status' => 'success',
            'description' => 'courses purchase',
            'type' => 'debit',
        ]);

        return response()->json($order->load('items'), 201);
    }






    public function verifyDiscountCode(Request $request)
    {
        // Validate the incoming request for discount code
        $validator = Validator::make($request->all(), [
            'discount_code' => 'required|string|max:50',
            'course_id' => 'required|exists:courses,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $course = Course::find($request->product_id);

        if ($course->discount_code && $course->discount_code === $request->discount_code) {
            if ($course->is_discounted) {
                return response()->json([
                    'success' => true,
                    'discount_percentage' => $course->discount_percentage,
                    'message' => 'Discount code is valid.',
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'The discount code is not applicable for this course.',
            ], 400);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid discount code for this course.',
        ], 400);
    }

    // public function userCourses(Request $request)
    // {
    //     $user = auth()->user();

    //     $courses = $user->bought_courses()
    //                     ->withPivot('quantity', 'purchase_price', 'course_order_id')
    //                     ->get();

    //     return response()->json($courses);
    // }


}
