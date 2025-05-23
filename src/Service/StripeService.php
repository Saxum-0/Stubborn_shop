<?php

namespace App\Service;

use Stripe\Stripe;
use Stripe\Checkout\Session;

class StripeService
{
    private string $secretKey;

    public function __construct(string $stripeSecretKey)
    {
        $this->secretKey = $stripeSecretKey;
    }

    public function createCheckoutSession(array $lineItems, string $successUrl, string $cancelUrl): Session
    {
        Stripe::setApiKey($this->secretKey);

        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);
    }
}
