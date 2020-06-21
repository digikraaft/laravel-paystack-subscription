<?php


namespace Digikraaft\PaystackSubscription\Tests\Feature;

class CustomerTest extends FeatureTestCase
{
    public function test_customers_in_paystack_can_be_updated()
    {
        $user = $this->createCustomer('paystack_customer_update');

        $user->createAsPaystackCustomer();

        $customer = $user->updatePaystackCustomer(['first_name' => 'JohnD'])->data;

        $this->assertEquals('JohnD', $customer->first_name);
    }
}
