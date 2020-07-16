# Paystack subscription in a Laravel application
Laravel Paystack Subscription offers a simple, fluent interface to [Paystack's](https://paystack.com/) subscription billing services. No need to worry about writing subscription billing code anymore!

![tests](https://github.com/digikraaft/laravel-paystack-subscription/workflows/tests/badge.svg)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)


# Installation

You can install the package via composer:

```bash
composer require digikraaft/laravel-paystack-subscription
```
#### Configuration File
The Laravel Paystack Subscription package comes with a configuration file, here is the content of the file:
```php
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
```
You can publish this config file with the following commands:
```bash
php artisan vendor:publish --provider="Digikraaft\PaystackSubscritpion\PaystackSubscritpionServiceProvider" --tag="config"
```

#### Database Migrations
You will need to publish the database migrations with these commands:
```bash
php artisan vendor:publish --provider="Digikraaft\PaystackSubscritpion\PaystackSubscritpionServiceProvider" --tag="migrations"
php artisan migrate
```
The migrations will add several columns to your users table as well 
as create a new subscriptions table to hold all of your customer's subscriptions.

# Configuration

### Billable Model
Before using the package, add the Billable trait to your model definition. 
This trait provides various methods to allow you to perform common billing tasks, such as creating subscriptions,
renewing subscriptions and cancelling subscriptions:

```php
use Digikraaft\PaystackSubscription\Billable;

class User extends Authenticatable
{
    use Billable;
}
```
The package assumes that your Billable model will be the default App\User class that ships with Laravel. 
If you wish to change this you can specify a different model in your .env file or in the published config
file of the package:

```dotenv
SUBSCRIPTION_MODEL = App\User
```
Please note that if you're using a model other than Laravel's supplied App\User model, 
you'll need to publish and alter the migrations provided to match your alternative model's table name.

You can also specify a different table name for your billable model.
See the [Configuration File](#configuration-file) section.

### API Keys
Next, you should configure your Paystack keys in your .env file. 
You can get your Paystack API keys from the Paystack dashboard.
```dotenv
PAYSTACK_PUBLIC_KEY=your-paystack-public-key
PAYSTACK_SECRET=your-paystack-secret
```

## Customers
### Retrieving Customers
You can retrieve a customer by their Paystack ID using the `PaystackSubscription::findBillable` method. 
This will return an instance of the Billable model:

```
use Digikraaft\PaystackSubscription\PaystackSubscription;

$user = PaystackSubscription::findBillable($paystackId);
```

### Creating Customers
If you need to create a Paystack customer without starting a subscription immediately,
you can do by using the `createAsPaystackCustomer` method:

```
$paystackCustomer = $user->createAsPaystackCustomer();
```
The code creates a customer in Paystack. You can then begin a subscription at a later date.
You can also use an optional `$options` array to pass in any additional parameters that are supported by the Paystack API:
```
$paystackCustomer = $user->createAsPaystackCustomer($options);
```
If the billable entity is already a customer in Paystack, you can retrieve the customer
object using the `asPaystackCustomer` method:
```
$paystackCustomer = $user->asPaystackCustomer();
```

If you are not sure that the billable entity is already a Paystack customer, the
`createOrGetPaystackCustomer` method can be used to the get the customer object. This method
will create a new customer in Paystack if one does not already exist:
```
$paystackCustomer = $user->createOrGetPaystackCustomer();
``` 

### Updating Customers
If you need to update the Paystack customer with additional information, you can do this using
the `updatePaystackCustomer` method:
```
$paystackCustomer = $user->updatePaystackCustomer($options);
```

## Subscriptions
### Creating Subscriptions
To create a subscription, first retrieve an instance of your billable model, which typically will be an instance of App\User. 
Once you have retrieved the model instance, you may use the newSubscription method to create the model's subscription:

```
$user = User::find(1);

$user->newSubscription('default', 'PLN_paystackplan_code')->create($transactionId);
```
The first argument passed to the `newSubscription` method should be the name of the subscription. 
If your application only offers a single subscription, you might call this `default` or `primary`. 
The second argument is the specific plan the user is subscribing to. 
This value should correspond to the plan's code in Paystack.

The `create` method, which accepts a Paystack transaction ID, 
will begin the subscription as well as update your database with the customer ID 
and other relevant billing information.

You can check the [implementation demo app](https://github.com/digikraaft/laravel-paystack-subscription-demo) to see how you can easily get this plan code and process payment to get
the `$transactionID` from Paystack.

### Authorization Code
When a customer is initially charged, Paystack creates an authorization code that can be used
later to bill the customer should the customer cancel.
If you would like to bill the customer at a later date, the authorization code can be passed as a second argument
to the `create` method:
```
$user->newSubscription('default', 'PLN_paystackplan_code')->create(null, $authorizationCode);
```

### Additional Details
If you would like to specify additional customer information, 
you may do so by passing them as the third argument to the `create` method:
```
$user->newSubscription('default', 'PLN_paystackplan_code')->create($transactionId, null, [
    'email' => $email,
]);
```
The additional fields specified must be supported by Paystack's API.

## Checking Subscription Status
Once a user is subscribed to your application, you may easily check their 
subscription status using a variety of convenient methods. 
First, the `subscribed` method returns `true` if the user has an active subscription, 
even if the subscription is cancelled and is due not to renew at the end of the billing period:
```
if ($user->subscribed('default')) {
    //
}
```
The `subscribed` method can also be used in a route middleware, 
allowing you to filter access to routes and controllers based on the user's subscription status:
```
public function handle($request, Closure $next)
{
    if ($request->user() && ! $request->user()->subscribed('default')) {
        // This user is not a paying customer...
        return redirect('billing');
    }

    return $next($request);
}
```
An example of this can be seen in the demo implementation of this package 
[here](https://github.com/digikraaft/laravel-paystack-subscription-demo)

The `subscribedToPlan` method may be used to determine if the user is subscribed 
to a given plan based on a given Paystack plan code. 
In this example, we will determine if the user's `default` subscription is actively subscribed to the `PLN_paystackplan_code` plan:
```
if ($user->subscribedToPlan('PLN_paystackplan_code', 'default')) {
    //
}
```

By passing an array to the `subscribedToPlan` method, 
you may determine if the user's `default` subscription 
is actively subscribed to the `PLN_paystackplan_code` or the `PLN_paystackplan_code2` plan:
```
if ($user->subscribedToPlan(['PLN_paystackplan_code', 'PLN_paystackplan_code2'], 'default')) {
    //
}
```
The `active` method may be used to determine if the user currently has an active subscription:
```
if ($user->subscription('default')->active()) {
    //
}
```

The `renews` method may be used to determine if the user's subscription will renew after the current billing period:
```
if ($user->subscription('default')->renews()) {
    //
}
```

The `daysLeft` method may be used to get the number of days left on the current billing period:
```
$daysLeft = $user->subscription('default')->daysLeft();

```

The `endsAt` method may be used to get the date the current billing period will end:
```
$endDate = $user->subscription('default')->endsAt();

```

### Cancelled Subscription Status
To determine if the user has cancelled their subscription, you may use the `isCancelled` method:
```
if ($user->subscription('default')->isCancelled()) {
    //
}
```

To determine if the user's subscription is past due, you may use the `pastDue` method:
```
if ($user->subscription('default')->pastDue()) {
    //
}
```
### Subscription Scopes
The `active` and` cancelled` subscription states are also available as query scopes so that you may easily 
query your database for subscriptions:
```
// Get all active subscriptions...
$subscriptions = Subscription::query()->active()->get();

// Get all of the cancelled subscriptions for a user...
$subscriptions = $user->subscriptions()->cancelled()->get();
```

## Cancelling Subscriptions
To cancel a subscription, call the `cancel` method on the user's subscription:
```
$user->subscription('default')->cancel();
```
When a subscription is cancelled, the `paystack_status` column in your database is set to `complete`. 
This is based on Paytstack's implementation, meaning the subscription will not renew at the end of the billing period.
The subscription continues to be active until the end of the current billing period.

## Resuming Subscriptions
If a user has cancelled their subscription and you wish to resume it, use the `enable` method.
```
$user->subscription('default')->enable();
```
If the user cancels a subscription and then resumes that subscription before the subscription has fully expired, 
they will not be billed immediately. Instead, their subscription will be re-activated,
and they will be billed on the original billing cycle.

# Handling Paystack Webhooks
Paystack can notify your application about various events via webhooks. 
By default, a route that points to this package's webhook controller is configured through the service provider. 
This controller will handle all incoming webhook requests.

By default, this controller will automatically handle cancelling subscriptions, enabling subscriptions and failed invoices.
This controller can be extended to handle any webhook event you like.

To ensure your application can handle Paystack webhooks, be sure to configure the webhook URL in the Paystack dashboard. 
By default, this package's webhook controller listens to the `/paystack/webhook`

### Webhooks & CSRF Protection
Since Paystack webhooks need to bypass Laravel's CSRF protection, be sure to list the URI as an exception in your 
`VerifyCsrfToken` middleware or list the route outside of the `web` middleware group:
```
protected $except = [
    'paystack/*',
];
```

### Defining Webhook Event Handlers
If you have additional webhook events you would like to handle, 
extend the Webhook controller. Your method names should correspond to this package's 
expected convention, specifically, methods should be prefixed with `handle` and 
the "camel case" name of the webhook you wish to handle. 
For example, if you wish to handle the `invoice.create` webhook, 
you should add a `handleInvoiceCreate` method to the controller:
```php
<?php

namespace App\Http\Controllers;

use Digikraaft\PaystackSubscription\Http\Controllers\WebhookController as PaystackWebhookController;

class WebhookController extends PaystackWebhookController
{
    /**
     * Handle invoice create.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleInvoiceCreate($payload)
    {
        // Handle The Event
    }
}
```
Next, define a route to your controller within your `routes/web.php` file. 
This will overwrite the default shipped route:

```
Route::post(
    'paystack/webhook',
    '\App\Http\Controllers\WebhookController@handleWebhook'
);
```
Under the hood, wehbook handling is done using [this package](https://github.com/digikraaft/laravel-paystack-webhooks)

You can find details about Paystack events [here](https://paystack.com/docs/payments/webhooks/#supported-events)

## Paystack API
If you would like to interact with the Paystack objects directly, 
you may conveniently retrieve them using the `asPaystack` method:

```
$paystackSubscription = $subscription->asPaystackSubscription();

$paystackSubscription->quantity = 2;

$paystackSubscription->save();
```
For details on how to interact with the object, check our `paystack-php` 
package [here](https://github.com/digikraaft/paystack-php)

## Testing
When testing an application that uses this package, you are free to mock the actual HTTP requests
to the Paystack API; however, this requires you to partially re-implement this package's 
own behavior. We therefore recommend allowing your tests to hit the actual 
Paystack API. While this is slower, it provides more confidence that your application
is working as expected and any slow tests may be placed within their own PHPUnit 
testing group.

When testing, remember that this package itself already has a great test suite, 
so you should only focus on testing the subscription and payment flow of your own 
application and not every underlying behavior of this package.

To get started, add the **testing** version of your Paystack secret and other required entities to your `phpunit.xml` file:
```
<env name="PAYSTACK_SECRET" value="sk_test_your_secret_key"/>
<env name="PAYSTACK_CUSTOMER" value="CUS_<customercode1>" />
<env name="PAYSTACK_OTHER_CUSTOMER" value="CUS_<customercode2>" />
<env name="PAYSTACK_PLAN" value="PLN_<plan_code1>" />
<env name="PAYSTACK_OTHER_PLAN" value="PLN_<plan_code2>" />
<env name="PAYSTACK_TRANSACTION_ID" value="<valid_transaction_id_used_for_plan_code1>" />
<env name="PAYSTACK_TRANSACTION_ID_INVALID" value="<existing_paystack_transaction_id>" />
<env name="PAYSTACK_TRANSACTION_REF" value="valid_transaction_reference_used_for_plan_code1" />
<env name="PAYSTACK_TRANSACTION_REF_INVALID" value="<existing_paystack_transaction_id>" />
<env name="DB_CONNECTION" value="testing"/>
```
When testing, ensure the environment variables above are created in paystack and correspond to
the description in the values.

Use the command below to run your tests:
``` bash
composer test
```
## More Good Stuff
Check [here](https://github.com/digikraaft) for more awesome free stuff!

## Alternatives
- [laravel-paystack](https://github.com/unicodeveloper/laravel-paystack)
- [Laravel Cashier - Paystack Edition](https://github.com/webong/cashier-paystack)

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email dev@digitalkraaft.com instead of using the issue tracker.

## Credits

- [Tim Oladoyinbo](https://github.com/timoladoyinbo)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
