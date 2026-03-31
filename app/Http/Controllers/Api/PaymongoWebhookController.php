<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Services\Contracts\CreditServiceInterface;
use App\Enums\TransactionStatus;

class PaymongoWebhookController extends Controller
{
    public function handle(Request $request)
    {
        if (!$this->verifySignature($request)) {
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $payload = $request->all();

        $eventType = data_get($payload, 'data.attributes.type');

        $paymentIntentId = data_get(
            $payload,
            'data.attributes.data.attributes.payment_intent_id'
        );

        if (!$paymentIntentId) {
            return response()->json(['error' => 'Missing payment_intent_id'], 400);
        }

        /**
         * =========================
         * PAYMENT SUCCESS
         * =========================
         */
        if ($eventType === 'payment.paid') {

            try {
                DB::transaction(function () use ($paymentIntentId) {

                    $transaction = Transaction::where('payment_intent_id', $paymentIntentId)
                        ->lockForUpdate()
                        ->first();

                    if (!$transaction) {
                        Log::warning('Transaction not found', compact('paymentIntentId'));
                        return;
                    }

                    // ✅ Idempotency: already processed
                    if ($transaction->status === TransactionStatus::SUCCESS) {
                        return;
                    }

                    // ✅ Apply credits (idempotent inside service)
                    app(CreditServiceInterface::class)
                        ->applyTopUp($transaction->user, $transaction->amount, $transaction);

                    // ✅ Mark success
                    $transaction->update([
                        'status' => TransactionStatus::SUCCESS,
                    ]);

                    Log::info('Topup completed', [
                        'transaction_id' => $transaction->id
                    ]);
                });

                return response()->json(['status' => 'processed']);

            } catch (\Throwable $e) {

                Log::error('Webhook error', [
                    'message' => $e->getMessage()
                ]);

                return response()->json(['error' => 'Server error'], 500);
            }
        }

        /**
         * =========================
         * PAYMENT FAILED
         * =========================
         */
        if ($eventType === 'payment.failed') {

            Transaction::where('payment_intent_id', $paymentIntentId)
                ->where('status', TransactionStatus::PENDING)
                ->update([
                    'status' => TransactionStatus::FAILED
                ]);

            return response()->json(['status' => 'failed']);
        }

        return response()->json(['status' => 'ignored']);
    }

    protected function verifySignature(Request $request): bool
    {
        $signatureHeader = $request->header('Paymongo-Signature');
        $secret = config('services.paymongo.webhook_secret');

        if (!$signatureHeader || !$secret) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $signatureHeader) as $segment) {
            [$key, $value] = array_pad(explode('=', $segment, 2), 2, null);
            $parts[$key] = $value;
        }

        if (!isset($parts['t']) || !isset($parts['te'])) {
            return false;
        }

        $timestamp = $parts['t'];
        $receivedSignature = $parts['te'];

        $payload = $timestamp . '.' . $request->getContent();

        $computedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($computedSignature, $receivedSignature);
    }
}
