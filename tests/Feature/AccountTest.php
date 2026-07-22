<?php

use A2ZWeb\BrandGeoClient\Enums\SubscriptionStatus;
use A2ZWeb\BrandGeoClient\Facades\BrandGeo;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;

it('maps the trial account with nested subscription, quota and usage', function () {
    Http::fake(['*' => Http::response(apiFixture('account'))]);

    $account = BrandGeo::account()->get();

    expect($account->id)->toBe(42)
        ->and($account->email)->toBe('jan@example.com')
        ->and($account->createdAt)->toBeInstanceOf(CarbonImmutable::class)
        ->and($account->subscription->status)->toBe(SubscriptionStatus::Trial)
        ->and($account->subscription->onTrial)->toBeTrue()
        ->and($account->subscription->trialDaysRemaining)->toBe(5)
        ->and($account->subscription->hasFullAccess)->toBeFalse()
        ->and($account->quota->planName)->toBe('Starter')
        ->and($account->quota->brands)->toBe(1)
        ->and($account->quota->trendHistoryDays)->toBe(30)
        ->and($account->usage->auditsThisMonth)->toBe(1)
        ->and($account->usage->auditsRemaining)->toBe(2);
});

it('maps unlimited quotas on free accounts as null', function () {
    Http::fake(['*' => Http::response(apiFixture('account-free'))]);

    $account = BrandGeo::account()->get();

    expect($account->subscription->status)->toBe(SubscriptionStatus::Free)
        ->and($account->subscription->hasFullAccess)->toBeTrue()
        ->and($account->quota->brands)->toBeNull()
        ->and($account->quota->auditsPerMonth)->toBeNull()
        ->and($account->quota->whiteLabel)->toBeTrue()
        ->and($account->usage->auditsRemaining)->toBeNull();
});
