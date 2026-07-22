<?php

use A2ZWeb\BrandGeoClient\BrandGeoClient;
use A2ZWeb\BrandGeoClient\Exceptions\MissingApiKeyException;
use A2ZWeb\BrandGeoClient\Facades\BrandGeo;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('sends the configured api key as a bearer token and accepts json', function () {
    Http::fake(['*' => Http::response(apiFixture('account'))]);

    BrandGeo::account()->get();

    Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', 'Bearer 1|test-key')
        && $request->hasHeader('Accept', 'application/json'));
});

it('uses the configured base url', function () {
    Http::fake(['*' => Http::response(apiFixture('account'))]);

    BrandGeo::account()->get();

    Http::assertSent(fn (Request $request) => str_starts_with($request->url(), 'https://brandgeo.test/api/v1/account'));
});

it('overrides the key with withApiKey without mutating the original client', function () {
    Http::fake(['*' => Http::response(apiFixture('account'))]);

    BrandGeo::withApiKey('2|other-key')->account()->get();

    Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', 'Bearer 2|other-key'));

    // Original singleton still uses the configured key.
    BrandGeo::account()->get();

    Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', 'Bearer 1|test-key'));
});

it('overrides the base url with withBaseUrl', function () {
    Http::fake(['*' => Http::response(apiFixture('account'))]);

    BrandGeo::withBaseUrl('https://staging.brandgeo.co/api/v1/')->account()->get();

    Http::assertSent(fn (Request $request) => str_starts_with($request->url(), 'https://staging.brandgeo.co/api/v1/account'));
});

it('throws MissingApiKeyException when no key is configured', function () {
    config()->set('brandgeo-client.api_key', null);
    $this->app->forgetInstance(BrandGeoClient::class);

    BrandGeo::clearResolvedInstances();

    BrandGeo::account()->get();
})->throws(MissingApiKeyException::class);

it('resolves the facade to the container singleton', function () {
    expect(BrandGeo::getFacadeRoot())->toBe(app(BrandGeoClient::class));
});
