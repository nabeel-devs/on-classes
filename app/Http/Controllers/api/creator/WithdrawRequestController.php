<?php

namespace App\Http\Controllers\api\creator;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\WithdrawRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreWithdrawRequest;
use App\Http\Resources\WithdrawRequestResource;

class WithdrawRequestController extends Controller
{
    public function store(StoreWithdrawRequest $request)
    {

        $user = Auth::user();

        // Check if the user has enough balance in their wallet
        if ($user->wallet_amount < $request->amount) {
            return response()->json(['error' => 'Insufficient wallet balance.'], 400);
        }
        $withdrawRequest = WithdrawRequest::create([
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'account_holder_name' => $request->account_holder_name,
            'ifsc_code' => $request->ifsc_code,
            'swift_code' => $request->swift_code,
            'status' => 'pending',
        ]);

        Transaction::create([
            'user_id' => $withdrawRequest->user_id,
            'amount' => $withdrawRequest->amount,
            'status' => 'success',
            'description' => 'Withdraw from wallet',
            'type' => 'debit',
        ]);

        $user->wallet_amount -= $request->amount;
        $user->save();

        return new WithdrawRequestResource($withdrawRequest);
    }

    /**
     * List all withdraw requests (for admin).
     */
    public function index()
    {

        $withdrawRequests = WithdrawRequest::with('user')->get();
        return WithdrawRequestResource::collection($withdrawRequests);
    }

    /**
     * List withdraw requests for the logged-in user.
     */
    public function userRequests()
    {
        $withdrawRequests = WithdrawRequest::where('user_id', Auth::id())->get();
        return WithdrawRequestResource::collection($withdrawRequests);
    }

    /**
     * View a specific withdraw request.
     */
    public function show($id)
    {
        $withdrawRequest = WithdrawRequest::with('user')->findOrFail($id);

        return new WithdrawRequestResource($withdrawRequest);
    }
}
