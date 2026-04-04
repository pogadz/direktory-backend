<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Credit;
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
        $this->authorize('view', Credit::class);

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
        $this->authorize('balance', Credit::class);

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
            'card_number' => 'required_if:payment_method_type,card',
            'exp_month' => 'required_if:payment_method_type,card',
            'exp_year' => 'required_if:payment_method_type,card',
            'cvc' => 'required_if:payment_method_type,card',
        ]);

        $this->authorize('topUp', Credit::class);

        $user = $request->user();
        $amount = $request->amount;

        $profileId = app(ProfileServiceInterface::class)
            ->getActiveProfileId($user);

        if (!$profileId) {
            return response()->json([
                'message' => 'No active profile found'
            ], 403);
        }

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

        // Create Payment Intent
        $intent = $this->payment->createPaymentIntent($amount);
        $paymentIntentId = data_get($intent, 'data.id');

        if (!$paymentIntentId) {
            return response()->json([
                'error' => 'Failed to create payment intent'
            ], 500);
        }

        // Create Transaction (pending)
        $transaction = $user->transactions()->create([
            'profile_id' => $profileId,
            'type' => \App\Enums\TransactionType::PAYMENT,
            'amount' => $amount,
            'status' => \App\Enums\TransactionStatus::PENDING,
            'payment_intent_id' => $paymentIntentId,
        ]);

        // Prepare card details if payment method is CARD
        $details = [];

        if ($request->payment_method_type === 'card') {
            $details = [
                'card_number' => preg_replace('/\D/', '', $request->card_number),
                'exp_month' => (int) $request->exp_month,
                'exp_year' => (int) $request->exp_year,
                'cvc' => $request->cvc,
            ];
        }

        // Pass $details as 3rd parameter
        $method = $this->payment->createPaymentMethod(
            $request->payment_method_type,
            $billing,
            $details // required for card
        );

        $paymentMethodId = data_get($method, 'data.id');

        if (!$paymentMethodId) {
            // Log PayMongo error response for debugging
            \Log::error('PayMongo createPaymentMethod failed', [
                'type' => $request->payment_method_type,
                'billing' => $billing,
                'details' => $details ?? null,
                'full_response' => $method,
            ]);

            return response()->json([
                'error' => 'Failed to create payment method'
            ], 500);
        }

        // Attach Payment Method
        $attached = $this->payment->attachPaymentMethod(
            $paymentIntentId,
            $paymentMethodId
        );

        $status = data_get($attached, 'data.attributes.status');
        $nextAction = data_get($attached, 'data.attributes.next_action');

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

        $this->authorize('refund', Credit::class);

        $user = $request->user();
        $amount = $request->amount;

        $transaction = $this->credit->refund($user, $amount);

        return response()->json([
            'message' => 'Credits refunded successfully',
            'transaction' => $transaction,
            'balance' => $user->creditBalance(),
        ]);
    }
}