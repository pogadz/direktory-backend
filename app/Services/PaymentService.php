<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Services\Contracts\PaymentServiceInterface;

class PaymentService implements PaymentServiceInterface
{
    protected $secretKey;

    public function __construct()
    {
        $this->secretKey = config('services.paymongo.secret_key');
    }

    protected function client()
    {
        return Http::withBasicAuth($this->secretKey, '')
            ->acceptJson()
            ->asJson();
    }

    public function createPaymentIntent(int $amount, string $currency = 'PHP'): mixed
    {
        return $this->client()->post('https://api.paymongo.com/v1/payment_intents', [
            'data' => [
                'attributes' => [
                    'amount' => $amount * 100, // PayMongo expects centavos
                    'payment_method_allowed' => ['card', 'gcash', 'paymaya'],
                    'currency' => $currency,
                    'capture_type' => 'automatic',
                ]
            ]
        ])->json();
    }

    /**
     * ✅ UPDATED: Added $details parameter for CARD support
     */
    public function createPaymentMethod(string $type, array $billing = [], array $details = []): mixed
    {
        $payload = [
            'data' => [
                'attributes' => [
                    'type' => $type,
                    'billing' => $billing,
                ],
            ],
        ];

        /**
         * ✅ FIX 1: Only add redirect for GCash / PayMaya
         * Card payments DO NOT use redirect here
         */
        if (in_array($type, ['gcash', 'paymaya'])) {
            $payload['data']['attributes']['redirect'] = [
                'return_url' => config('app.url') . '/api/payment/success'
            ];
        }

        /**
         * ✅ FIX 2: Add card details when type = card
         */
        if ($type === 'card') {
            $payload['data']['attributes']['details'] = [
                'card_number' => $details['card_number'] ?? null,
                'exp_month' => $details['exp_month'] ?? null,
                'exp_year' => $details['exp_year'] ?? null,
                'cvc' => $details['cvc'] ?? null,
            ];
        }

        /**
         * ✅ ADDED: Use centralized client() instead of new Http:: call
         */
        return $this->client()
            ->post('https://api.paymongo.com/v1/payment_methods', $payload)
            ->json();
    }

    public function attachPaymentMethod(string $paymentIntentId, string $paymentMethodId): mixed
    {
        return $this->client()->post(
            "https://api.paymongo.com/v1/payment_intents/{$paymentIntentId}/attach",
            [
                'data' => [
                    'attributes' => [
                        'payment_method' => $paymentMethodId,

                        /**
                         * ✅ OK: Needed for redirect-based + 3DS card flows
                         */
                        'return_url' => config('app.url') . '/api/payment/success',
                    ]
                ]
            ]
        )->json();
    }

    public function retrievePaymentIntent(string $id): mixed
    {
        return $this->client()
            ->get("https://api.paymongo.com/v1/payment_intents/{$id}")
            ->json();
    }
}
