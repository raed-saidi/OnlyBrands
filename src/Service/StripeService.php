<?php

namespace App\Service;

use Stripe\StripeClient;

class StripeService
{
    private $stripe;

    public function __construct(string $stripeSecretKey)
    {
        $this->stripe = new StripeClient($stripeSecretKey);
    }

    public function createPaymentIntent(int $amount, string $currency = 'eur')
    {
        return $this->stripe->paymentIntents->create([
            'amount' => $amount,
            'currency' => $currency,
            'payment_method_types' => ['card'],
        ]);
    }
}
