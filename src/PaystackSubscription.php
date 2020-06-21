<?php

namespace Digikraaft\PaystackSubscription;


class PaystackSubscription
{
    /**
     * Get the billable entity instance by Paystack ID.
     *
     * @param $paystackId
     * @return \Digikraaft\PaystackSubscription\Billable|null
     */
    public static function findBillable($paystackId)
    {
        if ($paystackId === null) {
            return;
        }

        $model = config('paystacksubscription.model', env('SUBSCRIPTION_MODEL'));

        return (new $model)->where('paystack_id', $paystackId)->first();
    }

}
