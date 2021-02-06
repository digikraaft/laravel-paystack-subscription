<?php

namespace Digikraaft\PaystackSubscription;

class PaystackSubscription
{
    const CANCELLED_STATUS = 'cancelled';
    const ACTIVE_STATUS = 'active';
    const COMPLETED_STATUS = 'complete';

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
