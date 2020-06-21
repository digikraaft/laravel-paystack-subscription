<?php


namespace Digikraaft\PaystackSubscription\Exceptions;

class PaymentFailure extends \Exception
{

    /**
     * Create a new PaymeentFailure instance.
     *
     * @param array|object $transaction
     * @return static
     */
    public static function incompleteTransaction($transaction)
    {
        return new static(
            "The attempted transaction `{$transaction->data->id}` on Paystack was incomplete. Please complete the transaction or initiate an new one."
        );
    }

    /**
     * Create a new PaymentFailure instance.
     *
     * @param array|object $transaction
     * @param $plan
     * @return static
     */
    public static function invalidTransactionPlan($transaction, $plan)
    {
        return new static(
            "The transaction `{$transaction->data->id}` does not belong to this plan `$plan`."
        );
    }
}
