<?php

use A2ZWeb\BrandGeoClient\Enums\AuditMode;
use A2ZWeb\BrandGeoClient\Enums\AuditStatus;
use A2ZWeb\BrandGeoClient\Enums\MonitorStatus;
use A2ZWeb\BrandGeoClient\Enums\PromptCategory;
use A2ZWeb\BrandGeoClient\Enums\Provider;
use A2ZWeb\BrandGeoClient\Enums\RecommendationPriority;
use A2ZWeb\BrandGeoClient\Enums\ReportStatus;
use A2ZWeb\BrandGeoClient\Enums\SubscriptionStatus;
use A2ZWeb\BrandGeoClient\Facades\BrandGeo;
use Illuminate\Support\Facades\Http;

it('hydrates values this client does not know to the Unknown case', function () {
    $fixture = apiFixture('audit-detailed-full');
    $fixture['data']['status'] = 'archived';
    $fixture['data']['reports'] = [[
        'uuid' => 'rrrr1111-1111-1111-1111-111111111111',
        'provider' => 'mistral',
        'mode' => 'hybrid',
        'status' => 'superseded',
    ]];
    $fixture['data']['recommendations']['data']['action_plan'][0]['priority'] = 'P9';
    Http::fake(['*' => Http::response($fixture)]);

    $audit = BrandGeo::audits()->get('x');
    $report = $audit->reports[0];

    expect($audit->status)->toBe(AuditStatus::Unknown)
        ->and($audit->isComplete())->toBeFalse()
        ->and($report->provider)->toBe(Provider::Unknown)
        ->and($report->mode)->toBe(AuditMode::Unknown)
        ->and($report->status)->toBe(ReportStatus::Unknown)
        ->and($report->isLocked())->toBeFalse()
        ->and($audit->recommendations->actionPlan[0]->priority)->toBe(RecommendationPriority::Unknown);
});

it('tolerates unknown monitor, category, run provider and subscription values', function () {
    $monitors = apiFixture('monitors');
    $monitors['data'][0]['status'] = 'hibernating';
    $templates = apiFixture('prompt-templates');
    $templates['data'][0]['category'] = 'pricing';
    $runs = apiFixture('runs-cursor-1');
    $runs['data'][0]['provider'] = 'mistral';
    $account = apiFixture('account');
    $account['data']['subscription']['status'] = 'paused';

    Http::fakeSequence()
        ->push($monitors)
        ->push($templates)
        ->push($runs)
        ->push($account);

    expect(BrandGeo::monitors()->list()->items[0]->status)->toBe(MonitorStatus::Unknown)
        ->and(BrandGeo::monitors()->promptTemplates('x')->items[0]->category)->toBe(PromptCategory::Unknown)
        ->and(BrandGeo::monitors()->runs('x')->items[0]->provider)->toBe(Provider::Unknown)
        ->and(BrandGeo::account()->get()->subscription->status)->toBe(SubscriptionStatus::Unknown);
});

it('keeps an unknown snapshot engine apart from the overall row', function () {
    $fixture = apiFixture('snapshots');
    $fixture['data'][1]['provider'] = 'mistral';
    Http::fake(['*' => Http::response($fixture)]);

    $items = BrandGeo::monitors()->snapshots('x', Provider::Gemini)->items;

    expect($items[0]->provider)->toBeNull()
        ->and($items[0]->isOverall())->toBeTrue()
        ->and($items[1]->provider)->toBe(Provider::Unknown)
        ->and($items[1]->isOverall())->toBeFalse();
});

it('leaves Unknown out of known()', function () {
    expect(Provider::known())->not->toContain(Provider::Unknown)
        ->and(Provider::known())->toHaveCount(count(Provider::cases()) - 1)
        ->and(Provider::Unknown->isUnknown())->toBeTrue()
        ->and(Provider::Openai->isUnknown())->toBeFalse();
});

it('refuses to filter by an Unknown case without calling the API', function () {
    Http::fake();

    expect(fn () => BrandGeo::audits()->list(status: AuditStatus::Unknown))
        ->toThrow(InvalidArgumentException::class, 'AuditStatus::Unknown');

    Http::assertNothingSent();
});
