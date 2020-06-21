<?php

namespace Digikraaft\PaystackSubscription\Tests\Unit;

use Digikraaft\PaystackSubscription\Exceptions\CustomerAlreadyExist;
use Digikraaft\PaystackSubscription\Exceptions\InvalidCustomer;
use Digikraaft\PaystackSubscription\PaystackSubscription;
use Digikraaft\PaystackSubscription\Tests\TestCase;
use Digikraaft\PaystackSubscription\Tests\User;

class CustomerTest extends TestCase
{
    public function test_we_can_determine_if_user_has_an_authorization_code()
    {
        $user = new User;
        $user->paystack_authorization = '1234abcdedf';

        $this->assertTrue($user->hasPaystackAuthorization());

        $user = new User;

        $this->assertFalse($user->hasPaystackAuthorization());
    }

    public function test_authorization_returns_null_when_the_user_is_not_a_customer_yet()
    {
        $user = new User;

        $this->assertNull($user->paystackAuthorization());
    }

    public function test_paystack_customer_method_throws_exception_when_paystack_id_is_not_set()
    {
        $user = new User;

        $this->expectException(InvalidCustomer::class);

        $user->asPaystackCustomer();
    }

    public function test_paystack_customer_cannot_be_created_when_paystack_id_is_already_set()
    {
        $user = new User();
        $user->paystack_id = 'foobar';

        $this->expectException(CustomerAlreadyExist::class);

        $user->createAsPaystackCustomer();
    }

    public function test_billable_model_can_be_found()
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

        $billable = PaystackSubscription::findBillable($user->paystack_id);
        $this->assertInstanceOf(User::class, $billable);

        $user->paystack_id = null;
        $user->save();
        $billable = PaystackSubscription::findBillable($user->paystack_id);
        $this->assertNull($billable);
    }

    public function test_it_runs_the_migrations()
    {
        $this->assertEquals([
            'id',
            'name',
            'email',
            'password',
            'remember_token',
            'created_at',
            'updated_at',
            'paystack_id',
            'paystack_authorization',
            'paystack_email_token',
        ], \Schema::getColumnListing('users'));
    }
}
