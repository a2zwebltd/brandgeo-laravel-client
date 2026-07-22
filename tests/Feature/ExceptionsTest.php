<?php

use A2ZWeb\BrandGeoClient\Exceptions\ApiException;
use A2ZWeb\BrandGeoClient\Exceptions\AuthenticationException;
use A2ZWeb\BrandGeoClient\Exceptions\NotFoundException;
use A2ZWeb\BrandGeoClient\Exceptions\RateLimitException;
use A2ZWeb\BrandGeoClient\Exceptions\SubscriptionRequiredException;
use A2ZWeb\BrandGeoClient\Exceptions\ValidationException;
use A2ZWeb\BrandGeoClient\Facades\BrandGeo;
use Illuminate\Support\Facades\Http;

it('maps 401 with the nonstandard nested error body', function () {
    Http::fake(['*' => Http::response(apiFixture('error-unauthorized'), 401)]);

    try {
        BrandGeo::account()->get();
        $this->fail('Expected AuthenticationException');
    } catch (AuthenticationException $e) {
        expect($e->getMessage())->toBe('Unauthorized')
            ->and($e->status)->toBe(401);
    }
});

it('maps 402 to SubscriptionRequiredException', function () {
    Http::fake(['*' => Http::response(['message' => 'Subscription required.'], 402)]);

    try {
        BrandGeo::audits()->get('some-uuid');
        $this->fail('Expected SubscriptionRequiredException');
    } catch (SubscriptionRequiredException $e) {
        expect($e->getMessage())->toBe('Subscription required.')
            ->and($e->status)->toBe(402);
    }
});

it('maps 404 to NotFoundException', function () {
    Http::fake(['*' => Http::response(['message' => 'Not Found'], 404)]);

    BrandGeo::brands()->get('missing-uuid');
})->throws(NotFoundException::class);

it('maps 422 to ValidationException with the errors bag', function () {
    Http::fake(['*' => Http::response(apiFixture('error-validation'), 422)]);

    try {
        BrandGeo::audits()->list();
        $this->fail('Expected ValidationException');
    } catch (ValidationException $e) {
        expect($e->errors)->toHaveKey('status')
            ->and($e->errors['status'][0])->toContain('invalid');
    }
});

it('maps 429 to RateLimitException with retry metadata', function () {
    Http::fake(['*' => Http::response(['message' => 'Too Many Attempts.'], 429, [
        'Retry-After' => '42',
        'X-RateLimit-Limit' => '30',
        'X-RateLimit-Remaining' => '0',
    ])]);

    try {
        BrandGeo::account()->get();
        $this->fail('Expected RateLimitException');
    } catch (RateLimitException $e) {
        expect($e->retryAfter)->toBe(42)
            ->and($e->limit)->toBe(30)
            ->and($e->remaining)->toBe(0);
    }
});

it('maps unexpected statuses to ApiException', function () {
    Http::fake(['*' => Http::response(['message' => 'Server Error'], 500)]);

    try {
        BrandGeo::account()->get();
        $this->fail('Expected ApiException');
    } catch (ApiException $e) {
        expect($e->status)->toBe(500);
    }
});
