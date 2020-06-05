<?php

namespace Digikraaft\PaystackSubscription;

use Digikraaft\PaystackSubscription\Commands\PaystackSubscriptionCommand;
use Illuminate\Support\ServiceProvider;

class PaystackSubscriptionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/paystacksubscription.php' => config_path('paystacksubscription.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../resources/views' => base_path('resources/views/vendor/paystacksubscription'),
            ], 'views');

            if (! class_exists('CreatePackageTables')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/paystacksubscription.php' => database_path('migrations/' . date('Y_m_d_His', time()) . 'paystacksubscription.php'),
                ], 'migrations');
            }

            $this->commands([
                PaystackSubscriptionCommand::class,
            ]);
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'subscription');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/paystacksubscription.php', 'paystacksubscription');
    }
}
