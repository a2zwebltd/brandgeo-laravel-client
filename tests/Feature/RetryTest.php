<?php

use A2ZWeb\BrandGeoClient\BrandGeoClient;
use A2ZWeb\BrandGeoClient\Exceptions\ApiException;
use A2ZWeb\BrandGeoClient\Exceptions\NotFoundException;
use A2ZWeb\BrandGeoClient\Exceptions\RateLimitException;
use A2ZWeb\BrandGeoClient\Exceptions\ValidationException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;

function retryingClient(int $times = 3): BrandGeoClient
{
    return new BrandGeoClient(
        http: app(Factory::class),
        apiKey: '1|test-key',
        baseUrl: 'https://brandgeo.test/api/v1',
        retryTimes: $times,
        retrySleep: 0,
    );
}

it('retries 5xx responses and returns the eventual success', function () {
    Http::fake(['*' => Http::sequence()
        ->push(['message' => 'Server Error'], 503)
        ->push(['message' => 'Server Error'], 500)
        ->push(apiFixture('account'))]);

    $account = retryingClient()->account()->get();

    expect($account)->not->toBeNull();
    Http::assertSentCount(3);
});

it('retries 429 responses', function () {
    Http::fake(['*' => Http::sequence()
        ->push(['message' => 'Too Many Attempts.'], 429)
        ->push(apiFixture('account'))]);

    retryingClient()->account()->get();

    Http::assertSentCount(2);
});

it('retries connection errors', function () {
    $attempts = 0;

    Http::fake(function () use (&$attempts) {
        if (++$attempts < 3) {
            throw new ConnectionException('Connection refused');
        }

        return Http::response(apiFixture('account'));
    });

    retryingClient()->account()->get();

    expect($attempts)->toBe(3);
});

it('does not retry deterministic 4xx responses', function (int $status, string $exception) {
    Http::fake(['*' => Http::response(['message' => 'nope', 'errors' => []], $status)]);

    expect(fn () => retryingClient()->brands()->get('missing'))->toThrow($exception);

    Http::assertSentCount(1);
})->with([
    '404' => [404, NotFoundException::class],
    '422' => [422, ValidationException::class],
]);

it('maps the final response to an exception once retries are exhausted', function (int $status, string $exception) {
    Http::fake(['*' => Http::response(['message' => 'still failing'], $status)]);

    expect(fn () => retryingClient(2)->account()->get())->toThrow($exception);

    Http::assertSentCount(2);
})->with([
    '503' => [503, ApiException::class],
    '429' => [429, RateLimitException::class],
]);
