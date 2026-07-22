<?php

use A2ZWeb\BrandGeoClient\Enums\RecommendationPriority;
use A2ZWeb\BrandGeoClient\Facades\BrandGeo;
use Illuminate\Support\Facades\Http;

it('maps a gated preview for trial accounts', function () {
    Http::fake(['*' => Http::response(apiFixture('audit-detailed'))]);

    $recommendations = BrandGeo::audits()->get('x')->recommendations;

    expect($recommendations->fullAccess)->toBeFalse()
        ->and($recommendations->isPreview())->toBeTrue()
        ->and($recommendations->lockedActions)->toBe(9)
        ->and($recommendations->overallScore)->toBe(6.5)
        ->and($recommendations->executiveSummary)->toContain('GEO gaps')
        ->and($recommendations->actionPlan)->toHaveCount(3)
        ->and($recommendations->actionPlan[0]->priority)->toBe(RecommendationPriority::P0)
        ->and($recommendations->actionPlan[0]->title)->toBe('Add Organization schema');
});

it('keeps the full document in raw for full-access accounts', function () {
    Http::fake(['*' => Http::response(apiFixture('audit-detailed-full'))]);

    $recommendations = BrandGeo::audits()->get('x')->recommendations;

    expect($recommendations->fullAccess)->toBeTrue()
        ->and($recommendations->isPreview())->toBeFalse()
        ->and($recommendations->lockedActions)->toBe(0)
        ->and($recommendations->raw)->toHaveKey('citation_gates')
        ->and($recommendations->raw)->toHaveKey('structured_data');
});
