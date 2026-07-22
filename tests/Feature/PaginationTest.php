<?php

use A2ZWeb\BrandGeoClient\Facades\BrandGeo;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('fetches the next page preserving per_page', function () {
    Http::fake([
        '*/brands?page=1*' => Http::response(apiFixture('brands-page-1')),
        '*/brands?page=2*' => Http::response(apiFixture('brands-page-2')),
        '*' => Http::response(apiFixture('brands-page-1')),
    ]);

    $first = BrandGeo::brands()->list(perPage: 2);

    expect($first->hasMorePages())->toBeTrue();

    $second = $first->nextPage();

    expect($second->currentPage)->toBe(2)
        ->and($second->hasMorePages())->toBeFalse()
        ->and($second->nextPage())->toBeNull()
        ->and($second->items[0]->name)->toBe('Third Brand');

    Http::assertSent(function (Request $request) {
        parse_str(parse_url($request->url(), PHP_URL_QUERY), $query);

        return $query['page'] === '2' && $query['per_page'] === '2';
    });
});

it('lazily iterates across all pages', function () {
    Http::fake(function (Request $request) {
        parse_str(parse_url($request->url(), PHP_URL_QUERY), $query);

        return Http::response(apiFixture(($query['page'] ?? '1') === '2' ? 'brands-page-2' : 'brands-page-1'));
    });

    $names = BrandGeo::brands()->list(perPage: 2)->lazy()->map(fn ($brand) => $brand->name)->all();

    expect($names)->toBe(['Acme', 'Widgets Co', 'Third Brand']);
});

it('follows the cursor until it runs out', function () {
    Http::fake(function (Request $request) {
        parse_str(parse_url($request->url(), PHP_URL_QUERY), $query);

        return Http::response(apiFixture(($query['cursor'] ?? null) === 'abc123' ? 'runs-cursor-2' : 'runs-cursor-1'));
    });

    $first = BrandGeo::monitors()->runs('m-uuid', perPage: 2);

    expect($first->hasMorePages())->toBeTrue()
        ->and($first->nextCursor)->toBe('abc123');

    $all = $first->lazy()->all();

    expect($all)->toHaveCount(3)
        ->and($all[2]->uuid)->toBe('dddd3333-3333-3333-3333-333333333333');

    $last = $first->nextPage();

    expect($last->hasMorePages())->toBeFalse()
        ->and($last->nextPage())->toBeNull();
});
