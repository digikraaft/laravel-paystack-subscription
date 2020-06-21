<?php


namespace Digikraaft\PaystackSubscription\Tests\Feature;

use Digikraaft\PaystackSubscription\Tests\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class WebhookControllerTest extends FeatureTestCase
{
    use WithoutMiddleware;

    /**
     * @var string
     */
    protected static $planId;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        static::$planId = getenv('PAYSTACK_PLAN');
    }

    public function test_subscription_is_updated()
    {
        config()->set(
            'paystacksubscription.model',
            User::class
        );

        $user = $this->createCustomer('subscription_is_updated', ['paystack_id' => 'cus_foo']);

        $subscription = $user->subscriptions()->create([
            'name' => 'main',
            'paystack_id' => 'sub_foo',
            'paystack_plan' => 'plan_foo',
            'paystack_status' => 'active',
            'ends_at' => \Carbon\Carbon::now(),
        ]);


        $resp = $this->postJson('paystack/webhook', [
            'event' => 'subscription.disable',
            'data' => [
                'id' => $subscription->paystack_id,
                'status' => 'complete',
                "next_payment_date" => "2020-07-20T07:00:00.000Z",
                'customer' => [
                    'customer_code' => 'cus_foo',
                ],
                'plan' => [
                    'plan_code' => 'plan_foo',
                ],
                'authorization' => [
                  'authorization_code' => 'AUTH_123abcdd',
                ],
                'subscription_code' => 'sub_foo',
            ],
        ])->assertOk();

        $this->assertDatabaseHas('dk_subscriptions', [
            'name' => 'main',
            'paystack_id' => $subscription->paystack_id,
            'paystack_plan' => $subscription->paystack_plan,
            'paystack_status' => 'complete',
        ]);
    }
}
