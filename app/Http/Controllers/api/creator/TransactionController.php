<?php

namespace App\Http\Controllers\api\creator;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Transaction::where('user_id', $user->id);

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

        return TransactionResource::collection($transactions);
    }

    public function walletInfo(Request $request)
{
    $user = auth()->user();



    $walletAmount = $user->wallet_amount;

    // Calculate total withdrawal amount
    $totalWithdrawAmount = Transaction::where('user_id', $user->id)
                                      ->where('type', 'debit')
                                      ->sum('amount');

    return response()->json([
        'wallet_amount' => $walletAmount,
        'total_withdraw_amount' => $totalWithdrawAmount
    ]);
}
}
