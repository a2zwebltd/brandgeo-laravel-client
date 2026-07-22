<?php

use A2ZWeb\BrandGeoClient\Enums\AuditMode;
use A2ZWeb\BrandGeoClient\Enums\Provider;
use A2ZWeb\BrandGeoClient\Enums\ReportStatus;
use A2ZWeb\BrandGeoClient\Facades\BrandGeo;
use Illuminate\Support\Facades\Http;

it('maps locked report stubs with everything else null', function () {
    Http::fake(['*' => Http::response(apiFixture('audit-reports-locked'))]);

    $reports = BrandGeo::audits()->reports('aaaa1111-1111-1111-1111-111111111111');

    expect($reports)->toHaveCount(5);

    $locked = $reports[1];

    expect($locked->isLocked())->toBeTrue()
        ->and($locked->status)->toBe(ReportStatus::Locked)
        ->and($locked->provider)->toBe(Provider::Openai)
        ->and($locked->mode)->toBe(AuditMode::Trained)
        ->and($locked->model)->toBeNull()
        ->and($locked->normalizedScore)->toBeNull()
        ->and($locked->grade)->toBeNull()
        ->and($locked->result)->toBeNull()
        ->and($locked->findings)->toBeNull();

    $done = $reports[0];

    expect($done->isLocked())->toBeFalse()
        ->and($done->normalizedScore)->toBe(72.5)
        ->and($done->grade)->toBe('B');
});

it('exposes the sanitized error on failed reports', function () {
    Http::fake(['*' => Http::response(apiFixture('audit-detailed'))]);

    $audit = BrandGeo::audits()->get('aaaa1111-1111-1111-1111-111111111111');
    $failed = $audit->reports[1];

    expect($failed->isFailed())->toBeTrue()
        ->and($failed->error)->toBe('The AI provider was temporarily unavailable.')
        ->and($failed->processedAt)->toBeNull();
});
