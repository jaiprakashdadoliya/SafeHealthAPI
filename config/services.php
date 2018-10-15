<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('SAFE_HEALTH_MAILGUN_DOMAIN'),
        'secret' => env('SAFE_HEALTH_MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SAFE_HEALTH_SES_KEY'),
        'secret' => env('SAFE_HEALTH_SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SAFE_HEALTH_SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('SAFE_HEALTH_STRIPE_KEY'),
        'secret' => env('SAFE_HEALTH_STRIPE_SECRET'),
    ],

];
