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

    public function createPaymentMethod(string $type, array $billing = []): mixed
    {
        return Http::withBasicAuth(config('services.paymongo.secret_key'), '')
            ->acceptJson()
            ->asJson()
            ->post('https://api.paymongo.com/v1/payment_methods', [
                'data' => [
                    'attributes' => [
                        'type' => $type,

                        'billing' => $billing,

                        // ✅ MUST be here for gcash/paymaya
                        'redirect' => [
                            'return_url' => config('app.url') . '/api/payment/success'
                        ],
                    ],
                ],
            ])->json();
    }

    public function attachPaymentMethod(string $paymentIntentId, string $paymentMethodId): mixed
    {
        return $this->client()->post(
            "https://api.paymongo.com/v1/payment_intents/{$paymentIntentId}/attach",
            [
                'data' => [
                    'attributes' => [
                        'payment_method' => $paymentMethodId,
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
