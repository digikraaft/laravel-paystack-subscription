<?php

namespace Digikraaft\PaystackSubscription\Traits;

use Digikraaft\Paystack\Customer as PaystackCustomer;
use Digikraaft\Paystack\Paystack;
use Digikraaft\PaystackSubscription\Exceptions\CustomerAlreadyExist;
use Digikraaft\PaystackSubscription\Exceptions\InvalidCustomer;

trait ManagesCustomer
{

    /**
     * Retrieve the Paystack customer ID.
     *
     * @return string|null
     */
    public function paystackId()
    {
        return $this->paystack_id;
    }

    /**
     * Determine if the entity has a Paystack customer ID.
     *
     * @return bool
     */
    public function hasPaystackId()
    {
        return ! is_null($this->paystack_id);
    }

    /**
     * Determine if the entity has a Paystack authorization.
     *
     * @return bool
     */
    public function hasPaystackAuthorization()
    {
        return ! is_null($this->paystack_authorization);
    }

    /**
     * Get Paystack authorization.
     *
     * @return string
     */
    public function paystackAuthorization()
    {
        return $this->paystack_authorization;
    }

    /**
     * Determine if the entity has a Paystack customer ID and throw an exception if not.
     *
     * @return void
     *
     * @throws \Digikraaft\PaystackSubscription\Exceptions\InvalidCustomer
     */
    protected function assertCustomerExists()
    {
        if (! $this->hasPaystackId()) {
            throw InvalidCustomer::doesNotExist($this);
        }
    }

    /**
     * Create a Paystack customer for the given model.
     *
     * @param  array  $options
     * @return \Digikraaft\Paystack\Customer
     *
     * @throws \Digikraaft\PaystackSubscription\Exceptions\CustomerAlreadyExist
     */
    public function createAsPaystackCustomer(array $options = [])
    {
        if ($this->hasPaystackId()) {
            throw CustomerAlreadyExist::exists($this);
        }

        if (! array_key_exists('email', $options) && $email = $this->paystackEmail()) {
            $options['email'] = $email;
        }

        // Here we will create the customer instance on Paystack and store the ID of the
        // user from Paystack. This ID will allow us to retrieve users from Paystack later when we need to work.
        Paystack::setApiKey(config('paystacksubscription.secret', env('PAYSTACK_SECRET')));
        $customer = PaystackCustomer::create(
            $options,
        );

        $this->paystack_id = $customer->data->customer_code;

        $this->save();

        return $customer;
    }

    /**
     * Update the Paystack customer information for the model.
     *
     * @param  array  $options
     * @return \Digikraaft\Paystack\Customer
     */
    public function updatePaystackCustomer(array $options = [])
    {
        Paystack::setApiKey(config('paystacksubscription.secret', env('PAYSTACK_SECRET')));

        return PaystackCustomer::update(
            $this->paystack_id,
            $options,
        );
    }

    /**
     * Get the Paystack customer instance for the current user or create one.
     *
     * @param array $options
     * @return \Digikraaft\Paystack\Customer
     * @throws \Digikraaft\PaystackSubscription\Exceptions\InvalidCustomer
     */
    public function createOrGetPaystackCustomer(array $options = [])
    {
        if ($this->hasPaystackId()) {
            return $this->asPaystackCustomer();
        }

        return $this->createAsPaystackCustomer($options);
    }

    /**
     * Get the Paystack customer for the model.
     *
     * @return \Digikraaft\Paystack\Customer
     * @throws \Digikraaft\PaystackSubscription\Exceptions\InvalidCustomer
     */
    public function asPaystackCustomer()
    {
        $this->assertCustomerExists();

        Paystack::setApiKey(config('paystacksubscription.secret', env('PAYSTACK_SECRET')));

        return PaystackCustomer::fetch($this->paystack_id);
    }


    /**
     * Get the email address used to create the customer in Paystack.
     *
     * @return string|null
     */
    public function paystackEmail()
    {
        return $this->email;
    }
}
