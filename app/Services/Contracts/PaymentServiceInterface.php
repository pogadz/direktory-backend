<?php

namespace App\Services\Contracts;

interface PaymentServiceInterface
{
    public function createPaymentIntent(int $amount, string $currency = 'PHP'): mixed;

    public function createPaymentMethod(string $type, array $billing = []): mixed;

    public function attachPaymentMethod(string $paymentIntentId, string $paymentMethodId): mixed;

    public function retrievePaymentIntent(string $id): mixed;
}
