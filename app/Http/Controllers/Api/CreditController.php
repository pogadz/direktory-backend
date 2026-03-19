<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\Contracts\CreditServiceInterface;

/**
 * @group Credit
 */
class CreditController extends Controller
{
    protected CreditServiceInterface $creditService;

    public function __construct(CreditServiceInterface $creditService)
    {
        $this->creditService = $creditService;
    }

    /**
     * List all credits / transactions for the user
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $credits = $user->credits()
            ->with('transaction', 'reference')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($credits);
    }

    /**
     * Get current user's credit balance
     */
    public function balance(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'balance' => $user->creditBalance(),
        ]);
    }

    /**
     * Top up credits
     */
    public function topUp(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:1',
        ]);

        $user = $request->user();
        $amount = $request->input('amount');

        $transaction = $this->creditService->topUp($user, $amount);

        return response()->json([
            'message' => 'Credits added successfully',
            'transaction' => $transaction,
            'balance' => $user->creditBalance(),
        ]);
    }

    /**
     * Refund credits
     */
    public function refund(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:1',
        ]);

        $user = $request->user();
        $amount = $request->input('amount');

        $transaction = $this->creditService->refund($user, $amount);

        return response()->json([
            'message' => 'Credits refunded successfully',
            'transaction' => $transaction,
            'balance' => $user->creditBalance(),
        ]);
    }
}