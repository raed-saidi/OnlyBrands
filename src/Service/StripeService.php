<?php

namespace App\Service;

use Stripe\Stripe;
use Stripe\PaymentIntent;

class StripeService
{
    public function __construct(string $secretKey)
    {
        Stripe::setApiKey($secretKey);
    }

    public function createPaymentIntent(int $amount): PaymentIntent
    {
        return PaymentIntent::create([
            'amount' => $amount,
            'currency' => 'tnd', // ou 'eur', selon ton besoin
            'payment_method_types' => ['card'],
        ]);
    }
}
