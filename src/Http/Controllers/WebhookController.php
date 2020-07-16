<?php
namespace Digikraaft\PaystackSubscription\Http\Controllers;

use Digikraaft\PaystackSubscription\PaystackSubscription;
use Digikraaft\PaystackWebhooks\Http\Controllers\WebhooksController as WebhooksController;

class WebhookController extends WebhooksController
{
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
}
