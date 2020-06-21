<?php


namespace Digikraaft\PaystackSubscription\Http\Controllers;

use Digikraaft\PaystackSubscription\Events\WebhookHandled;
use Digikraaft\PaystackSubscription\Events\WebhookReceived;
use Digikraaft\PaystackSubscription\Http\Middleware\VerifyWebhookSignature;
use Digikraaft\PaystackSubscription\PaystackSubscription;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends Controller
{
    /**
     * Create a new WebhookController instance.
     *
     * @return void
     */
    public function __construct()
    {
        if (config('paystacksubscription.secret', env('PAYSTACK_SECRET'))) {
            $this->middleware(VerifyWebhookSignature::class);
        }
    }

    /**
     * Handle Paystack webhook call.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleWebhook(Request $request)
    {
        $payload = json_decode($request->getContent(), true);
        $method = 'handle'.Str::studly(str_replace('.', '_', $payload['event']));

        WebhookReceived::dispatch($payload);

        if (method_exists($this, $method)) {
            $response = $this->{$method}($payload);

            WebhookHandled::dispatch($payload);

            return $response;
        }

        return $this->missingMethod();
    }

    /**
     * Handle disabled customer subscription.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleSubscriptionDisable(array $payload)
    {
        if ($user = $this->getUserByPaystackId($payload['data']['customer']['customer_code'])) {
            $user->subscriptions->filter(function ($subscription) use ($payload) {
                return $subscription->paystack_id === $payload['data']['subscription_code'];
            })->each(function ($subscription) use ($payload) {
                if (isset($payload['data']['status'])) {
                    $subscription->paystack_status = $payload['data']['status'];
                    $subscription->ends_at = $payload['data']['next_payment_date'];

                    $subscription->save();

                    return;
                }
            });
        }

        return $this->successMethod();
    }

    /**
     * Handle enabled customer subscription.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleSubscriptionEnable(array $payload)
    {
        if ($user = $this->getUserByPaystackId($payload['data']['customer']['customer_code'])) {
            $user->subscriptions->filter(function ($subscription) use ($payload) {
                return $subscription->paystack_id === $payload['data']['subscription_code'];
            })->each(function ($subscription) use ($payload) {
                if (isset($payload['data']['status'])) {
                    $subscription->paystack_status = $payload['data']['status'];
                    $subscription->ends_at = $payload['data']['next_payment_date'];

                    $subscription->save();

                    return;
                }
            });
        }

        return $this->successMethod();
    }

    /**
     * Handle a failed Invoice from a Paystack subscription.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleInvoiceFailed(array $payload)
    {
        if ($user = $this->getUserByPaystackId($payload['data']['customer']['customer_code'])) {
            $user->subscriptions->filter(function ($subscription) use ($payload) {
                return $subscription->paystack_id === $payload['data']['subscription_code'];
            })->each(function ($subscription) use ($payload) {
                if (isset($payload['data']['status'])) {
                    $subscription->paystack_status = $payload['data']['status'];
                    $subscription->ends_at = $payload['data']['next_payment_date'];

                    $subscription->save();

                    return;
                }
            });
        }

        return $this->successMethod();
    }

    /**
     * Get the billable entity instance by Paystack ID.
     *
     * @param string|null $paystackId
     * @return \Digikraaft\PaystackSubscription\Billable|null
     */
    protected function getUserByPaystackId(string $paystackId)
    {
        return PaystackSubscription::findBillable($paystackId);
    }

    /**
     * Handle successful calls on the controller.
     *
     * @param  array  $parameters
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function successMethod($parameters = [])
    {
        return new Response('Webhook Handled', 200);
    }

    /**
     * Handle calls to missing methods on the controller.
     *
     * @param  array  $parameters
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function missingMethod($parameters = [])
    {
        return new Response;
    }
}
