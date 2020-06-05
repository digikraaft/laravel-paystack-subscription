<?php

namespace Digikraaft\PaystackSubscription;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Digikraaft\PaystackSubscription\PaystackSubscription
 */
class PaystackSubscriptionFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'paystack-subscritpion';
    }
}
