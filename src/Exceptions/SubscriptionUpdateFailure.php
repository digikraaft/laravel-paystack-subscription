<?php


namespace Digikraaft\PaystackSubscription\Exceptions;

class SubscriptionUpdateFailure extends \Exception
{
    /**
     * Create a new SubscriptionUpdateFailure instance.
     *
     * @param  array|object  $subscription
     * @param  string  $plan
     * @return static
     */
    public static function duplicatePlan($subscription, $plan)
    {
        return new static(
            "The plan \"$plan\" is already attached to subscription \"{$subscription->paystack_id}\"."
        );
    }

    /**
     * Create a new SubscriptionUpdateFailure instance.
     *
     * @return static
     */
    public static function invalidCustomer()
    {
        return new static(
            "The transaction does not belong to this customer."
        );
    }

    /**
     * Create a new SubscriptionUpdateFailure instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model $owner
     * @param  string  $plan
     * @return static
     */
    public static function duplicateSubscription($owner, $plan)
    {
        return new static(
            "The user \"{$owner->name}\" is already subscribed to the plan \"{$plan}\"."
        );
    }
}
