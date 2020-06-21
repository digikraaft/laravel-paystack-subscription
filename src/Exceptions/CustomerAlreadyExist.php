<?php


namespace Digikraaft\PaystackSubscription\Exceptions;

class CustomerAlreadyExist extends \Exception
{

    /**
     * Create a new CustomerAlreadyExist instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $owner
     * @return static
     */
    public static function exists($owner)
    {
        return new static(class_basename($owner)." is already a Paystack customer with ID {$owner->paystack_id}.");
    }
}
