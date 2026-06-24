<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payment Simulation Mode
    |--------------------------------------------------------------------------
    |
    | When enabled (the default for this project), the gateways run in a
    | simulation mode using deterministic success rates. Disable this in
    | production to require real gateway credentials and live API calls.
    |
    */

    'simulate' => (bool) env('PAYMENT_SIMULATE', true),

    /*
    |--------------------------------------------------------------------------
    | Default success rates (simulation only)
    |--------------------------------------------------------------------------
    |
    | Percentage chance (0-100) that a simulated payment succeeds.
    | Ignored entirely when PAYMENT_SIMULATE=false.
    |
    */

    'success_rates' => [
        'paypal' => (int) env('PAYPAL_SUCCESS_RATE', 90),
        'credit_card' => (int) env('CREDIT_CARD_SUCCESS_RATE', 85),
        'stripe' => (int) env('STRIPE_SUCCESS_RATE', 95),
    ],

    /*
    |--------------------------------------------------------------------------
    | PayPal Gateway
    |--------------------------------------------------------------------------
    |
    | Credentials for the PayPal REST API. Used when simulation is disabled.
    |
    */

    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'secret' => env('PAYPAL_SECRET'),
        'mode' => env('PAYPAL_MODE', 'sandbox'), // sandbox | live
    ],

    /*
    |--------------------------------------------------------------------------
    | Credit Card Gateway
    |--------------------------------------------------------------------------
    |
    | Provider-agnostic credit card processor credentials.
    |
    */

    'credit_card' => [
        'provider' => env('CREDIT_CARD_PROVIDER'),
        'api_key' => env('CREDIT_CARD_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Stripe Gateway (future)
    |--------------------------------------------------------------------------
    |
    | Placeholder credentials for the Stripe gateway, ready to be wired up
    | when the StripeGateway is implemented (see README -> Extensibility).
    |
    */

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

];
