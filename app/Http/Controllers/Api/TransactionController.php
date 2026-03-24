<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Contracts\TransactionRepositoryInterface;

/**
 * @group Transaction
 */
class TransactionController extends Controller
{
    protected TransactionRepositoryInterface $transactions;

    public function __construct(TransactionRepositoryInterface $transactions)
    {
        $this->transactions = $transactions;
    }

    /**
     * List all transactions
     */
    public function index(Request $request)
    {
        $transactions = $this->transactions->getAllByUser($request->user()->id);

        return response()->json($transactions);
    }

    /**
     * Get a specific transaction
     * 
     * @urlParam id integer required The ID of the profile. Example: 0
     */
    public function show($id)
    {
        $transaction = $this->transactions->findById($id);

        if (!$transaction) {
            return response()->json([
                'message' => 'Transaction not found',
            ], 404);
        }

        return response()->json($transaction);
    }

    /**
     * Get all transactions by profile
     * 
     * @urlParam profile_id integer required The ID of the profile. Example: 0
     */
    public function getByProfile($profile_id)
    {
        $transactions = $this->transactions->getByProfile($profile_id);

        if ($transactions->isEmpty()) {
            return response()->json([
                'message' => 'Transactions not found',
            ], 404);
        }

        return response()->json($transactions);
    }
}