<?php

namespace Digikraaft\PaystackSubscription\Tests\Unit;

use Digikraaft\PaystackSubscription\Events\WebhookHandled;
use Digikraaft\PaystackSubscription\Events\WebhookReceived;
use Digikraaft\PaystackSubscription\Http\Controllers\WebhookController;
use Digikraaft\PaystackSubscription\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Symfony\Component\HttpFoundation\Response;

class WebhookControllerTest extends TestCase
{
    public function test_proper_methods_are_called_based_on_paystack_event()
    {
        $request = $this->request('subscription.disable');

        Event::fake([
            WebhookHandled::class,
            WebhookReceived::class,
        ]);

        $response = (new WebhookControllerTestStub)->handleWebhook($request);

        Event::assertDispatched(WebhookReceived::class, function (WebhookReceived $event) use ($request) {
            return $request->getContent() == json_encode($event->payload);
        });

        Event::assertDispatched(WebhookHandled::class, function (WebhookHandled $event) use ($request) {
            return $request->getContent() == json_encode($event->payload);
        });

        $this->assertEquals('Webhook Handled', $response->getContent());
    }

    public function test_normal_response_is_returned_if_method_is_missing()
    {
        $request = $this->request('foo.bar');

        Event::fake([
            WebhookHandled::class,
            WebhookReceived::class,
        ]);

        $response = (new WebhookControllerTestStub)->handleWebhook($request);

        Event::assertDispatched(WebhookReceived::class, function (WebhookReceived $event) use ($request) {
            return $request->getContent() == json_encode($event->payload);
        });

        Event::assertNotDispatched(WebhookHandled::class);

        $this->assertEquals(200, $response->getStatusCode());
    }

    private function request($event)
    {
        return Request::create(
            '/paystack',
            'POST',
            [],
            [],
            [],
            [],
            json_encode(['event' => $event, 'data' => 'domain'])
        );
    }
}

class WebhookControllerTestStub extends WebhookController
{
    public function __construct()
    {
    }

    public function handleSubscriptionDisable(array $payload)
    {
        return new Response('Webhook Handled', 200);
    }
}
