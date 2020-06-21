<?php


namespace Digikraaft\PaystackSubscription\Tests\Feature;

use Digikraaft\PaystackSubscription\Http\Middleware\VerifyWebhookSignature;
use Digikraaft\PaystackSubscription\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class VerifyWebhookSignatureTest extends TestCase
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function setUp(): void
    {
        parent::setUp();

        $this->request = new Request([], [], [], [], [], [], 'Signed Body');
    }

    public function test_request_is_rejected_when_paystack_headers_are_not_set()
    {
        $this->expectException(AccessDeniedHttpException::class);
        (new VerifyWebhookSignature())
            ->handle($this->request, function ($request) {
            });
    }

    public function test_request_is_rejected_when_paystack_headers_are_invalid()
    {
        $this->expectException(AccessDeniedHttpException::class);
        $this->request->setMethod('post');
        $this->request->headers->set('HTTP_X_PAYSTACK_SIGNATURE', 'some_random_signature');
        (new VerifyWebhookSignature())
            ->handle($this->request, function ($request) {
            });
    }
}
