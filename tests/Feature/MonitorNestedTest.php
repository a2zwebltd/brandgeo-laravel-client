<?php

use A2ZWeb\BrandGeoClient\Enums\PromptCategory;
use A2ZWeb\BrandGeoClient\Enums\Provider;
use A2ZWeb\BrandGeoClient\Facades\BrandGeo;
use A2ZWeb\BrandGeoClient\Resources\MonitorsResource;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('lists competitors', function () {
    Http::fake(['*' => Http::response(apiFixture('competitors'))]);

    $page = BrandGeo::monitors()->competitors('m-uuid');

    expect($page)->toHaveCount(2)
        ->and($page->items[0]->name)->toBe('Rival Inc')
        ->and($page->items[1]->url)->toBeNull();
});

it('lists prompt templates with the is_active filter as a boolean string', function () {
    Http::fake(['*' => Http::response(apiFixture('prompt-templates'))]);

    $page = BrandGeo::monitors()->promptTemplates('m-uuid', isActive: true);

    expect($page->items[0]->category)->toBe(PromptCategory::Discovery)
        ->and($page->items[1]->category)->toBe(PromptCategory::UseCase)
        ->and($page->items[1]->isCustom)->toBeTrue();

    Http::assertSent(function (Request $request) {
        parse_str(parse_url($request->url(), PHP_URL_QUERY), $query);

        return $query['is_active'] === '1';
    });
});

it('serializes run filters: enum, booleans and dates', function () {
    Http::fake(['*' => Http::response(apiFixture('runs-cursor-1'))]);

    BrandGeo::monitors()->runs(
        'm-uuid',
        provider: Provider::Gemini,
        brandMentioned: false,
        template: 10,
        from: new DateTimeImmutable('2026-07-01 15:30:00'),
        to: '2026-07-20',
    );

    Http::assertSent(function (Request $request) {
        parse_str(parse_url($request->url(), PHP_URL_QUERY), $query);

        return $query['provider'] === 'gemini'
            && $query['brand_mentioned'] === '0'
            && $query['template'] === '10'
            && $query['from'] === '2026-07-01'
            && $query['to'] === '2026-07-20';
    });
});

it('maps prompt runs', function () {
    Http::fake(['*' => Http::response(apiFixture('runs-cursor-1'))]);

    $runs = BrandGeo::monitors()->runs('m-uuid');

    expect($runs->items[0]->provider)->toBe(Provider::Gemini)
        ->and($runs->items[0]->brandMentioned)->toBeTrue()
        ->and($runs->items[0]->brandPosition)->toBe(1)
        ->and($runs->items[0]->sentimentScore)->toBe(0.8)
        ->and($runs->items[1]->brandMentioned)->toBeFalse()
        ->and($runs->items[1]->brandPosition)->toBeNull();
});

it('passes the provider=all passthrough on snapshots', function () {
    Http::fake(['*' => Http::response(apiFixture('snapshots'))]);

    BrandGeo::monitors()->snapshots('m-uuid', provider: MonitorsResource::PROVIDER_ALL);

    Http::assertSent(function (Request $request) {
        parse_str(parse_url($request->url(), PHP_URL_QUERY), $query);

        return $query['provider'] === 'all';
    });
});

it('maps the trend result with applied and max days', function () {
    Http::fake(['*' => Http::response(apiFixture('trend'))]);

    $trend = BrandGeo::monitors()->trend('m-uuid', days: 365);

    expect($trend)->toHaveCount(2)
        ->and($trend->daysApplied)->toBe(30)
        ->and($trend->daysMax)->toBe(30)
        ->and($trend->points[0]->date->toDateString())->toBe('2026-07-13')
        ->and(iterator_to_array($trend))->toHaveCount(2);

    Http::assertSent(function (Request $request) {
        parse_str(parse_url($request->url(), PHP_URL_QUERY), $query);

        return $query['days'] === '365';
    });
});
