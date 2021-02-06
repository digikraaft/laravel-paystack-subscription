<?php

namespace Digikraaft\PaystackSubscription\Tests\Feature;

use Digikraaft\PaystackSubscription\Exceptions\PaymentFailure;
use Digikraaft\PaystackSubscription\Subscription;
use Digikraaft\PaystackSubscription\Tests\User;
use GuzzleHttp\Exception\ClientException;

class SubscriptionTest extends FeatureTestCase
{
    /**
     * @var string
     */
    protected static $planId;

    /**
     * @var string
     */
    protected static $otherPlanId;


    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        static::$planId = getenv('PAYSTACK_PLAN');

        static::$otherPlanId = getenv('PAYSTACK_OTHER_PLAN');
    }

//    public function test_subscriptions_can_be_created()
//    {
//        config()->set(
//            'paystacksubscription.model',
//            User::class
//        );
//        $user = $this->createCustomer('subscriptions_can_be_created');
//
//        // Create Subscription
//        $subscription = $user->newSubscription('main', static::$planId)->create(getenv("PAYSTACK_TRANSACTION_ID"));
//
//        $this->assertDatabaseHas('dk_subscriptions', [
//            'user_id' => 1,
//        ]);
//
//        // Cancel Subscription
//        $subscription->cancel();
//
//        $this->assertTrue($subscription->active());
//        $this->assertTrue($subscription->isCancelled());
//        $this->assertFalse($subscription->renews());
//        $this->assertFalse($subscription->pastDue());
//
//        $subscription->enable();
//    }

    public function test_invalid_transaction_or_authorization_results_in_an_exception()
    {
        $user = $this->createCustomer('invalid_transaction_or_authorization');

        try {
            $user->newSubscription('main', static::$planId)->create(env('PAYSTACK_TRANSACTION_ID_INVALID'));

            $this->fail('Expected exception '.PaymentFailure::class.' was not thrown.');
        } catch (ClientException $e) {
            $this->assertNotInstanceOf(Subscription::class, $subscription = $user->subscription('main'));
        } catch (PaymentFailure $e) {
            // Assert subscription was not added to the billable entity.
            $this->assertNotInstanceOf(Subscription::class, $subscription = $user->subscription('main'));
        }
    }
}
