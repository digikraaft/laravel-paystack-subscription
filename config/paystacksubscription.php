<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Paystack Keys
    |--------------------------------------------------------------------------
    |
    | The Paystack publishable key and secret key. You can get these from your Paystack dashboard.
    |
    */

    'public_key' => env('PAYSTACK_PUBLIC_KEY'),

    'secret' => env('PAYSTACK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Subscription Model
    |--------------------------------------------------------------------------
    |
    | This is the model in your application that implements the Billable trait
    | provided by PaystackSubscription. It will serve as the primary model you use while
    | interacting with PaystackSubscription related methods, subscriptions, etc.
    |
    */
    'model' => env('SUBSCRIPTION_MODEL', App\User::class),

    /*
   |--------------------------------------------------------------------------
   | User Table
   |--------------------------------------------------------------------------
   |
   | This is the table in your application where your users' information is stored.
   |
   */
    'user_table' => 'users',

    /*
   |--------------------------------------------------------------------------
   | Webhooks Path
   |--------------------------------------------------------------------------
   |
   | This is the base URI path where webhooks will be handled from.
   |
   | This should correspond to the webhooks URL set in your Paystack dashboard:
   | https://dashboard.paystack.com/#/settings/developer.
   |
   | If your webhook URL is https://domain.com/paystack/webhook/ then you should simply enter paystack here.
   |
   | Remember to also add this as an exception in your VerifyCsrfToken middleware.
   |
   | See the demo application linked on github to help you get started.
   |
   */
    'webhook_path' => env('PAYSTACK_WEBHOOK_PATH', 'paystack'),

];
