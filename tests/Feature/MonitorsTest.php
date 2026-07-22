<?php

use A2ZWeb\BrandGeoClient\Enums\MonitorStatus;
use A2ZWeb\BrandGeoClient\Facades\BrandGeo;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('lists monitors with status filter serialized', function () {
    Http::fake(['*' => Http::response(apiFixture('monitors'))]);

    $page = BrandGeo::monitors()->list(status: MonitorStatus::Active);

    expect($page->items[0]->status)->toBe(MonitorStatus::Active)
        ->and($page->items[0]->brand->name)->toBe('Acme')
        ->and($page->items[0]->latestSnapshot)->toBeNull();

    Http::assertSent(function (Request $request) {
        parse_str(parse_url($request->url(), PHP_URL_QUERY), $query);

        return $query['status'] === 'active';
    });
});

it('maps a detailed monitor with the overall latest snapshot', function () {
    Http::fake(['*' => Http::response(apiFixture('monitor-detailed'))]);

    $monitor = BrandGeo::monitors()->get('bbbb1111-1111-1111-1111-111111111111');

    expect($monitor->latestSnapshot)->not->toBeNull()
        ->and($monitor->latestSnapshot->isOverall())->toBeTrue()
        ->and($monitor->latestSnapshot->provider)->toBeNull()
        ->and($monitor->latestSnapshot->visibilityScore)->toBe(64.2)
        ->and($monitor->latestSnapshot->sentiment->positive)->toBe(12)
        ->and($monitor->latestSnapshot->sentiment->netScore)->toBe(36.7)
        ->and($monitor->recommendations->isPreview())->toBeTrue();
});
