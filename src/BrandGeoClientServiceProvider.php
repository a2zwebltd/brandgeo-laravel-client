<?php

namespace A2ZWeb\BrandGeoClient;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\ServiceProvider;

class BrandGeoClientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/brandgeo-client.php', 'brandgeo-client');

        $this->app->singleton(BrandGeoClient::class, function (Application $app) {
            $config = $app['config']['brandgeo-client'];

            return new BrandGeoClient(
                http: $app->make(HttpFactory::class),
                apiKey: $config['api_key'] ?? null,
                baseUrl: rtrim($config['base_url'] ?? 'https://brandgeo.co/api/v1', '/'),
                timeout: (int) ($config['timeout'] ?? 30),
                verify: (bool) ($config['verify'] ?? true),
                retryTimes: (int) ($config['retry']['times'] ?? 0),
                retrySleep: (int) ($config['retry']['sleep'] ?? 200),
            );
        });
    }

    public function boot(): void
    {
        $this->registerPublishing();
    }

    private function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/brandgeo-client.php' => config_path('brandgeo-client.php'),
        ], 'brandgeo-client-config');
    }
}
