<?php

namespace Digikraaft\PaystackSubscription;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PaystackSubscriptionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerPublishables();

        $this->registerRoutes();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/paystacksubscription.php', 'paystacksubscription');
    }

    protected function registerPublishables(): void
    {
        $this->publishes([
            __DIR__.'/../config/paystacksubscription.php' => config_path('paystacksubscription.php'),
        ], 'config');

        if (! class_exists('CreateSubscriptionTables')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_subscription_tables.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_subscription_tables.php'),
            ], 'migrations');
        }
    }

    protected function registerRoutes()
    {
        //load routes for webhooks
        Route::group([
            'prefix' => config('paystacksubscription.webhook_path'),
            'namespace' => 'Digikraaft\PaystackSubscription\Http\Controllers',
            'as' => 'paystack.',
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }
}
