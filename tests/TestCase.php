<?php

namespace Digikraaft\PaystackSubscription\Tests;

use Digikraaft\PaystackSubscription\PaystackSubscriptionServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        $this->withFactories(__DIR__.'/database/factories');
    }

    protected function getPackageProviders($app)
    {
        return [
            PaystackSubscriptionServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

//        include_once __DIR__.'/../database/migrations/create_subscriptions_tables.php';
//           (new CreatePackageTables())->up();

    }
}
