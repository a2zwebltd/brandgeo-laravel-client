<?php

use A2ZWeb\BrandGeoClient\Enums\AuditStatus;
use A2ZWeb\BrandGeoClient\Enums\MonitorStatus;
use A2ZWeb\BrandGeoClient\Facades\BrandGeo;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('lists brands with nested summaries', function () {
    Http::fake(['*' => Http::response(apiFixture('brands-page-1'))]);

    $page = BrandGeo::brands()->list(perPage: 2);

    expect($page)->toHaveCount(2)
        ->and($page->total)->toBe(3)
        ->and($page->items[0]->name)->toBe('Acme')
        ->and($page->items[0]->latestAudit->status)->toBe(AuditStatus::Done)
        ->and($page->items[0]->latestAudit->overallScore)->toBe(72.5)
        ->and($page->items[0]->monitor->status)->toBe(MonitorStatus::Active)
        ->and($page->items[1]->industry)->toBeNull()
        ->and($page->items[1]->latestAudit)->toBeNull()
        ->and($page->items[1]->monitor)->toBeNull();

    Http::assertSent(fn (Request $request) => $request['page'] == 1 && $request['per_page'] == 2);
});

it('gets a single brand', function () {
    Http::fake(['*' => Http::response(apiFixture('brand'))]);

    $brand = BrandGeo::brands()->get('11111111-1111-1111-1111-111111111111');

    expect($brand->uuid)->toBe('11111111-1111-1111-1111-111111111111')
        ->and($brand->url)->toBe('https://acme.example');

    Http::assertSent(fn (Request $request) => str_contains($request->url(), '/brands/11111111-1111-1111-1111-111111111111'));
});
