<?php


namespace Digikraaft\PaystackSubscription;

use Digikraaft\Paystack\Paystack;
use Digikraaft\Paystack\Transaction;
use GuzzleHttp\Exception\ClientException;

class Payment
{
    /**
     * Determine if the transaction is valid.
     *
     * @param  string  $trandactionRef
     * @return array|object|bool
     */
    public static function hasValidTransaction(string $trandactionRef)
    {
        Paystack::setApiKey(config('paystacksubscription.secret', env('PAYSTACK_SECRET')));

        try {
            $transaction = Transaction::verify($trandactionRef);
            if ($transaction->status && $transaction->data->status == 'success') {
                return $transaction;
            }
        } catch (ClientException $exception) {
            return false;
        }

        return false;
    }
}
