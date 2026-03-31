<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\CreditToppedUp;
use App\Services\Contracts\CreditServiceInterface;
use App\Services\Contracts\ProfileServiceInterface;
use App\Services\Contracts\PaymentServiceInterface;
use Illuminate\Http\Request;

/**
 * @group Credit
 */
class CreditController extends Controller
{
    protected CreditServiceInterface $credit;
    protected PaymentServiceInterface $payment;

    public function __construct(
        CreditServiceInterface $creditService,
        PaymentServiceInterface $paymentService
    )
    {
        $this->credit = $creditService;
        $this->payment = $paymentService;
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
     * 
     * @bodyParam amount integer required Example: 100
     * @bodyParam payment_method_type string required Example: gcash
     */
    public function topUp(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:1',
            'payment_method_type' => 'required|in:gcash,paymaya,card',
        ]);

        $user = $request->user();
        $amount = $request->amount;

        $profileId = app(ProfileServiceInterface::class)
            ->getActiveProfileId($user);

        // Bypass mode (for testing)
        if (config('services.paymongo.payment_bypass')) {
            dd('testing');

            $transaction = $this->credit->topUp($user, $amount, null, $profileId);
            $balance = $user->creditBalance();

            $user->notify(new CreditToppedUp($amount, $balance));

            return response()->json([
                'message' => 'Credits added successfully',
                'transaction' => $transaction,
                'balance' => $balance,
            ]);
        }

        // Build billing from authenticated user
        $billing = [
            'name' => $user->firstname . ' ' . $user->lastname,
            'email' => $user->email,
            'phone' => $user->phone,
        ];

        if (!$billing['phone']) {
            return response()->json([
                'error' => 'User phone number is required'
            ], 422);
        }

        // 1. Create Payment Intent
        $intent = $this->payment->createPaymentIntent($amount);
        $paymentIntentId = data_get($intent, 'data.id');

        if (!$paymentIntentId) {
            return response()->json([
                'error' => 'Failed to create payment intent'
            ], 500);
        }

        // 2. Create Transaction (pending)
        $transaction = $user->transactions()->create([
            'profile_id' => $profileId,
            'type' => \App\Enums\TransactionType::PAYMENT,
            'amount' => $amount,
            'status' => \App\Enums\TransactionStatus::PENDING,
            'payment_intent_id' => $paymentIntentId,
        ]);

        /**
         * 3. Create Payment Method (uses billing from user)
         */
        $method = $this->payment->createPaymentMethod(
            $request->payment_method_type,
            $billing
        );

        $paymentMethodId = data_get($method, 'data.id');

        if (!$paymentMethodId) {
            return response()->json([
                'error' => 'Failed to create payment method'
            ], 500);
        }

        /**
         * 4. Attach Payment Method
         */
        $attached = $this->payment->attachPaymentMethod(
            $paymentIntentId,
            $paymentMethodId
        );

        $status = data_get($attached, 'data.attributes.status');
        $nextAction = data_get($attached, 'data.attributes.next_action');

        /**
         * 5. Return response (NO CREDIT YET — handled by webhook)
         */
        return response()->json([
            'message' => 'Payment initiated',
            'transaction_id' => $transaction->id,
            'payment_intent_id' => $paymentIntentId,
            'status' => $status,
            'next_action' => $nextAction,
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

        $transaction = $this->credit->refund($user, $amount);

        return response()->json([
            'message' => 'Credits refunded successfully',
            'transaction' => $transaction,
            'balance' => $user->creditBalance(),
        ]);
    }
}