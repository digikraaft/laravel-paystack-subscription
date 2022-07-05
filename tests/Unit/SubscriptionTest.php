<?php

namespace Digikraaft\PaystackSubscription\Tests\Unit;

use Carbon\Carbon;
use Digikraaft\PaystackSubscription\Exceptions\SubscriptionUpdateFailure;
use Digikraaft\PaystackSubscription\Subscription;
use Digikraaft\PaystackSubscription\Tests\TestCase;
use Digikraaft\PaystackSubscription\Tests\User;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionTest extends TestCase
{
    public function test_we_can_check_if_a_subscription_is_past_due()
    {
        $subscription = new Subscription([
            'paystack_status' => 'complete',
            'ends_at' => Carbon::yesterday(),
        ]);
        $this->assertTrue($subscription->pastDue());
        $this->assertFalse($subscription->isCancelled());
        $this->assertFalse($subscription->renews());
        $this->assertFalse($subscription->valid());
    }

    public function test_we_can_check_if_a_subscription_is_active()
    {
        $subscription = new Subscription([
            'paystack_status' => 'active',
            'ends_at' => Carbon::today(),
        ]);

        $this->assertFalse($subscription->isCancelled());
        $this->assertTrue($subscription->active());
    }

    public function test_a_past_due_subscription_is_not_valid()
    {
        $subscription = new Subscription([
            'paystack_status' => 'complete',
            'ends_at' => Carbon::yesterday(),
        ]);

        $this->assertFalse($subscription->valid());
    }

    public function test_an_active_subscription_is_valid()
    {
        $subscription = new Subscription(['paystack_status' => 'active']);

        $this->assertTrue($subscription->valid());
    }

    public function test_we_can_check_number_of_days_to_end_of_subscription()
    {
        $subscription = new Subscription([
            'paystack_status' => 'active',
            'ends_at' => Carbon::now()->addDays(2),
        ]);

        $this->assertEquals(1, $subscription->daysLeft());
    }

    public function test_that_it_can_return_user_that_owns_the_subscription()
    {
        $user = User::create(array_merge([
            'email' => "foo@digitalkraaft.com",
            'name' => 'Tim Oladoyinbo',
            'password' => '$2y$10$FP/OV6lGytaM2tV78R6hAe1h/k2Gy20IHFZZtsH2M5fqVY/vHR7Le',
            'paystack_id' => 'CUS_abcd1234',
        ]));

        config()->set(
            'paystacksubscription.model',
            User::class
        );

        $subscription = new Subscription([
            'user_id' => $user->id,
        ]);

        $owner = $subscription->user();
        $this->assertInstanceOf(BelongsTo::class, $owner);
    }

    public function test_that_it_can_get_subscription_plan()
    {
        $subscription = new Subscription([
            'paystack_plan' => 'PLN_abcd1234',
        ]);

        $this->assertTrue($subscription->hasPlan('PLN_abcd1234'));
    }

    public function test_that_it_can_determine_if_subscription_renews()
    {
        $subscription = new Subscription([
            'paystack_status' => 'active',
            'ends_at' => Carbon::now()->addDays(2),
        ]);

        $this->assertTrue($subscription->renews('PLN_abcd1234'));
    }

    public function test_that_it_can_return_query_scopes()
    {
        $subscription = new Subscription([
            'paystack_status' => 'active',
            'ends_at' => Carbon::now()->addDays(2),
        ]);

        $this->assertInstanceOf(Builder::class, Subscription::query()->active());

        $subscription->update(['paystack_status' => 'complete']);
        $this->assertInstanceOf(Builder::class, Subscription::query()->cancelled());
    }

    public function test_that_exception_is_thrown_for_invalid_transaction()
    {
        $user = User::create(array_merge([
            'email' => "foo@digitalkraaft.com",
            'name' => 'Tim Oladoyinbo',
            'password' => '$2y$10$FP/OV6lGytaM2tV78R6hAe1h/k2Gy20IHFZZtsH2M5fqVY/vHR7Le',
            'paystack_id' => env('PAYSTACK_CUSTOMER'),
        ]));

        config()->set(
            'paystacksubscription.model',
            User::class
        );

        $this->expectException(ClientException::class);
        $user->newSubscription('main', env('PAYSTACK_PLAN'))
        ->create(env('PAYSTACK_TRANSACTION_ID_INVALID'));
    }

    public function test_that_exception_is_thrown_when_transaction_is_used_for_another_customer()
    {
        $user = User::create(array_merge([
            'email' => "foo@digitalkraaft.com",
            'name' => 'Tim Oladoyinbo',
            'password' => '$2y$10$FP/OV6lGytaM2tV78R6hAe1h/k2Gy20IHFZZtsH2M5fqVY/vHR7Le',
            'paystack_id' => env('PAYSTACK_OTHER_CUSTOMER'),
        ]));

        config()->set(
            'paystacksubscription.model',
            User::class
        );

        $this->expectException(SubscriptionUpdateFailure::class);
        $user->newSubscription('main', env('PAYSTACK_PLAN'))
            ->create(env('PAYSTACK_TRANSACTION_ID'));
    }
}
