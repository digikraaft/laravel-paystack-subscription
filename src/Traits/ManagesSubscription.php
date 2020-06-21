<?php

namespace Digikraaft\PaystackSubscription\Traits;

use Digikraaft\PaystackSubscription\Subscription;
use Digikraaft\PaystackSubscription\SubscriptionBuilder;

trait ManagesSubscription
{

    /**
     * Begin creating a new subscription.
     *
     * @param  string  $name
     * @param  string  $plan
     * @return \Digikraaft\PaystackSubscription\SubscriptionBuilder
     */
    public function newSubscription($name, $plan)
    {
        return new SubscriptionBuilder($this, $name, $plan);
    }

    /**
     * Determine if the Paystack model has a given subscription.
     *
     * @param  string  $name
     * @param  string|null  $plan
     * @return bool
     */
    public function subscribed($name = 'default', $plan = null)
    {
        $subscription = $this->subscription($name);

        if (! $subscription || ! $subscription->valid()) {
            return false;
        }

        return $plan ? $subscription->hasPlan($plan) : true;
    }

    /**
     * Get a subscription instance by name.
     *
     * @param  string  $name
     * @return \Digikraaft\PaystackSubscription\Subscription|null
     */
    public function subscription($name = 'default')
    {
        return $this->subscriptions->sortByDesc(function (Subscription $subscription) {
            return $subscription->created_at->getTimestamp();
        })->first(function (Subscription $subscription) use ($name) {
            return $subscription->name === $name;
        });
    }

    /**
     * Get all of the subscriptions for the Paystack model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, $this->getForeignKey())->orderBy('created_at', 'desc');
    }

    /**
     * Determine if the Paystack model is actively subscribed to one of the given plans.
     *
     * @param  string  $plans
     * @param  string  $name
     * @return bool
     */
    public function subscribedToPlan($plans, $name = 'default')
    {
        $subscription = $this->subscription($name);

        if (! $subscription || ! $subscription->valid()) {
            return false;
        }

        foreach ((array) $plans as $plan) {
            if ($subscription->hasPlan($plan)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the entity has a valid subscription on the given plan.
     *
     * @param  string  $plan
     * @return bool
     */
    public function onPlan($plan)
    {
        return ! is_null($this->subscriptions->first(function (Subscription $subscription) use ($plan) {
            return $subscription->valid() && $subscription->hasPlan($plan);
        }));
    }
}
