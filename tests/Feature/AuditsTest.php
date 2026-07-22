<?php

use A2ZWeb\BrandGeoClient\Enums\AuditStatus;
use A2ZWeb\BrandGeoClient\Facades\BrandGeo;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('serializes list filters and omits nulls', function () {
    Http::fake(['*' => Http::response(apiFixture('audits'))]);

    BrandGeo::audits()->list(brand: '11111111-1111-1111-1111-111111111111', status: AuditStatus::Done);

    Http::assertSent(function (Request $request) {
        parse_str(parse_url($request->url(), PHP_URL_QUERY), $query);

        return $query['brand'] === '11111111-1111-1111-1111-111111111111'
            && $query['status'] === 'done'
            && $query['page'] === '1';
    });
});

it('omits null filters from the query string entirely', function () {
    Http::fake(['*' => Http::response(apiFixture('audits'))]);

    BrandGeo::audits()->list();

    Http::assertSent(function (Request $request) {
        parse_str(parse_url($request->url(), PHP_URL_QUERY), $query);

        return ! array_key_exists('brand', $query) && ! array_key_exists('status', $query);
    });
});

it('maps a list audit without reports or recommendations', function () {
    Http::fake(['*' => Http::response(apiFixture('audits'))]);

    $audit = BrandGeo::audits()->list()->items[0];

    expect($audit->status)->toBe(AuditStatus::Done)
        ->and($audit->isComplete())->toBeTrue()
        ->and($audit->overallScore)->toBe(72.5)
        ->and($audit->brand->name)->toBe('Acme')
        ->and($audit->reports)->toBeNull()
        ->and($audit->recommendations)->toBeNull();
});

it('maps a detailed audit with reports and share url', function () {
    Http::fake(['*' => Http::response(apiFixture('audit-detailed'))]);

    $audit = BrandGeo::audits()->get('aaaa1111-1111-1111-1111-111111111111');

    expect($audit->reports)->toHaveCount(2)
        ->and($audit->shareUrl)->toBe('https://brandgeo.test/r/tok_abc')
        ->and($audit->recommendations)->not->toBeNull();
});
