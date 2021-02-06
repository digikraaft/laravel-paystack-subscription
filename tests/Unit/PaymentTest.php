<?php


namespace Digikraaft\PaystackSubscription\Tests\Unit;

use Digikraaft\PaystackSubscription\Payment;
use Digikraaft\PaystackSubscription\Tests\TestCase;
use GuzzleHttp\Exception\ClientException;

class PaymentTest extends TestCase
{
    public function test_payment_returns_false_for_invalid_transaction()
    {
        $transaction = Payment::hasValidTransaction(env('PAYSTACK_TRANSACTION_REF_INVALID'));
        $this->assertFalse($transaction);
    }

    public function test_payment_returns_valid_transaction()
    {
        $transaction = Payment::hasValidTransaction(env('PAYSTACK_TRANSACTION_REF'));
        $this->assertTrue($transaction->status);
    }
}
