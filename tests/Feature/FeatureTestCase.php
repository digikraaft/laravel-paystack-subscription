<?php


namespace Digikraaft\PaystackSubscription\Tests\Feature;


use Illuminate\Database\Eloquent\Model as Eloquent;
use Digikraaft\PaystackSubscription\Tests\User;
use Digikraaft\PaystackSubscription\Tests\TestCase;
use Digikraaft\Paystack\Paystack;

abstract class FeatureTestCase extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Paystack::setApiKey(getenv('PAYSTACK_SECRET'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        Eloquent::unguard();
    }

    protected function createCustomer($description = 'tim', $options = []): User
    {
        return User::create(array_merge([
            'email' => "{$description}@digitalkraaft.com",
            'name' => 'Tim Oladoyinbo',
            'password' => '$2y$10$FP/OV6lGytaM2tV78R6hAe1h/k2Gy20IHFZZtsH2M5fqVY/vHR7Le',
        ], $options));
    }
}
