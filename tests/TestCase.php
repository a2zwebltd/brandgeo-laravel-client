<?php

namespace A2ZWeb\BrandGeoClient\Tests;

use A2ZWeb\BrandGeoClient\BrandGeoClientServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            BrandGeoClientServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('brandgeo-client.api_key', '1|test-key');
        $app['config']->set('brandgeo-client.base_url', 'https://brandgeo.test/api/v1');
    }
}
