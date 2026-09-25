# BrandGEO Laravel Client

[![Packagist Version](https://img.shields.io/packagist/v/a2zwebltd/brandgeo-laravel-client.svg)](https://packagist.org/packages/a2zwebltd/brandgeo-laravel-client)
[![Downloads](https://img.shields.io/packagist/dt/a2zwebltd/brandgeo-laravel-client.svg)](https://packagist.org/packages/a2zwebltd/brandgeo-laravel-client)
![PHP](https://img.shields.io/badge/PHP-%5E8.2-blue)
![Laravel](https://img.shields.io/badge/Laravel-11%20%7C%2012%20%7C%2013-blue)

Official Laravel SDK for the [BrandGEO](https://brandgeo.co) public API — your brands, AI visibility audits and monitoring data as typed, readonly DTOs.

## Features

- **Typed everything** — readonly DTOs and string-backed enums for every resource; dates as `CarbonImmutable`.
- **Full API surface** — account & subscription, brands, audits with per-engine reports (six visibility dimensions, findings, GEO action plan), monitors with competitors, prompt runs, snapshots, share of voice and trends.
- **Pagination that scales** — page & cursor paginators with `nextPage()` and `lazy()` auto-pagination via `LazyCollection`.
- **Typed errors** — one exception class per API error (401/402/404/422/429/5xx) with rate-limit metadata on 429s.
- **Multi-account ready** — immutable `withApiKey()` clones for agency apps managing many BrandGEO accounts.
- **Test-friendly** — routes through Laravel's HTTP factory, so `Http::fake()` just works.

## Requirements

- PHP 8.2+
- Laravel 11 / 12 / 13

## Installation

```bash
composer require a2zwebltd/brandgeo-laravel-client
```

Generate an API key at [Settings → API](https://brandgeo.co/settings/api) in your BrandGEO dashboard, then add it to `.env`:

```dotenv
BRANDGEO_API_KEY=1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

Optionally publish the config (`base_url`, timeout, retries, TLS verify):

```bash
php artisan vendor:publish --tag=brandgeo-client-config
```

## Quick start

```php
use A2ZWeb\BrandGeoClient\Facades\BrandGeo;

$account = BrandGeo::account()->get();

$account->subscription->status;   // SubscriptionStatus::Trial|Active|Free|Expired
$account->quota->auditsPerMonth;  // null = unlimited
$account->usage->auditsRemaining;
```

Prefer dependency injection? Type-hint `A2ZWeb\BrandGeoClient\BrandGeoClient` — it's a container singleton.

## Usage

### Brands

```php
$page = BrandGeo::brands()->list(perPage: 50);

foreach ($page as $brand) {
    $brand->name;
    $brand->latestAudit?->overallScore;   // 0–100
    $brand->monitor?->status;             // MonitorStatus enum
}

$brand = BrandGeo::brands()->get($uuid);
```

### Audits

```php
use A2ZWeb\BrandGeoClient\Enums\AuditStatus;

$audits = BrandGeo::audits()->list(brand: $brandUuid, status: AuditStatus::Done);

$audit = BrandGeo::audits()->get($uuid);   // embeds reports + recommendations

foreach ($audit->reports as $report) {
    if ($report->isLocked()) {
        // Trial paywall: only uuid/provider/mode/status are available.
        continue;
    }

    $report->provider;          // Provider enum
    $report->normalizedScore;   // 0–100
    $report->grade;             // A–F
    $report->result;            // per-section scoring + analysis (array)
}
```

Poll a running audit cheaply:

```php
do {
    sleep(5);
    $reports = BrandGeo::audits()->reports($uuid);
    $pending = collect($reports)->filter(
        fn ($r) => in_array($r->status->value, ['queued', 'processing'])
    );
} while ($pending->isNotEmpty());
```

Recommendations are gated by plan — trial accounts get a preview:

```php
$rec = $audit->recommendations;

if ($rec?->isPreview()) {
    $rec->actionPlan;      // top ~25% of the plan (min 3 items)
    $rec->lockedActions;   // how many more are behind the paywall
} else {
    $rec?->raw;            // the full GEO document (citation gates, JSON-LD, roadmap…)
}
```

### Monitors

```php
use A2ZWeb\BrandGeoClient\Enums\Provider;
use A2ZWeb\BrandGeoClient\Resources\MonitorsResource;

$monitor = BrandGeo::monitors()->get($uuid);
$monitor->latestSnapshot?->visibilityScore;

BrandGeo::monitors()->competitors($uuid);
BrandGeo::monitors()->promptTemplates($uuid, isActive: true);

BrandGeo::monitors()->runs(
    $uuid,
    provider: Provider::Gemini,
    brandMentioned: true,
    from: now()->subWeek(),
);

// Snapshots default to the overall (cross-engine) rows — provider === null.
BrandGeo::monitors()->snapshots($uuid);
BrandGeo::monitors()->snapshots($uuid, provider: Provider::Openai);
BrandGeo::monitors()->snapshots($uuid, provider: MonitorsResource::PROVIDER_ALL);

$trend = BrandGeo::monitors()->trend($uuid, days: 90);
$trend->daysApplied;   // clamped to your plan's trend-history window
foreach ($trend as $point) {
    [$point->date, $point->visibilityScore];
}
```

## Forward compatibility

When BrandGEO adds a new engine, status or category before you upgrade the package, the DTO holds the enum's `Unknown` case instead of failing the call. Give your `match` expressions a `default` arm, and build UI lists from `Enum::known()`, which leaves `Unknown` out:

```php
use A2ZWeb\BrandGeoClient\Enums\Provider;

$report->provider->isUnknown();   // true for an engine this version doesn't know yet
Provider::known();                // every engine except Unknown
```

`Unknown` can't be used as a filter. Passing it throws `InvalidArgumentException` before any request is sent.

## Pagination

`foreach ($page)` iterates the fetched page only. `nextPage()` fetches the next one; `lazy()` auto-paginates:

```php
$page = BrandGeo::brands()->list();

while ($page !== null) {
    foreach ($page as $brand) { /* … */ }
    $page = $page->nextPage();
}

// Or let LazyCollection walk every page for you:
BrandGeo::audits()->list()->lazy()->each(fn ($audit) => /* … */);
BrandGeo::monitors()->runs($uuid)->lazy()->take(500)->filter(fn ($r) => $r->brandMentioned);
```

## Multiple accounts (agencies)

`withApiKey()` returns an immutable clone — the configured singleton is never mutated:

```php
foreach ($customers as $customer) {
    $client = BrandGeo::withApiKey($customer->brandgeo_api_key);
    $client->brands()->list();
}
```

## Error handling

All API errors extend `A2ZWeb\BrandGeoClient\Exceptions\BrandGeoException` (`$status`, `$body`):

| Exception | Status | Notes |
| --- | --- | --- |
| `MissingApiKeyException` | — | no key configured |
| `AuthenticationException` | 401 | invalid or revoked key |
| `SubscriptionRequiredException` | 402 | trial expired — detail endpoints paywalled; `/account` + lists still work |
| `NotFoundException` | 404 | missing **or owned by another account** (indistinguishable by design) |
| `ValidationException` | 422 | invalid query params — see `$errors` |
| `RateLimitException` | 429 | see `$retryAfter`, `$limit`, `$remaining` |
| `ApiException` | other | 5xx / unexpected |

```php
use A2ZWeb\BrandGeoClient\Exceptions\RateLimitException;

try {
    $audit = BrandGeo::audits()->get($uuid);
} catch (RateLimitException $e) {
    sleep($e->retryAfter ?? 60);
    // retry…
}
```

Connection failures throw Laravel's `Illuminate\Http\Client\ConnectionException`.

Set `BRANDGEO_RETRY_TIMES` to retry transient failures (connection errors, 5xx and 429). Other 4xx responses are never retried. The value is the total number of attempts, so `3` means up to two retries.

## Testing

The client routes through Laravel's HTTP factory, so `Http::fake()` just works:

```php
use Illuminate\Support\Facades\Http;

Http::fake([
    'brandgeo.co/api/v1/account' => Http::response(['data' => [/* … */]]),
    'brandgeo.co/api/v1/brands*' => Http::response(['data' => [], 'links' => [], 'meta' => []]),
]);
```

Run the package's own suite with `composer test` (Pest + Testbench, fully `Http::fake()`d).

## AI agents (Laravel Boost)

This package ships a [Laravel Boost](https://github.com/laravel/boost) skill, `brandgeo-sdk`, that teaches AI coding agents the client's entry points, pagination, exception map, score scales and testing patterns. It requires Boost 2 or newer:

```bash
composer require laravel/boost --dev
php artisan boost:install          # first time
php artisan boost:update --discover   # already installed
```

Select `a2zwebltd/brandgeo-laravel-client` when Boost lists third-party packages.

Boost only scans your app's **direct** Composer dependencies. If you only pull the client in through [`a2zwebltd/brandgeo-laravel-nova`](https://github.com/a2zwebltd/brandgeo-laravel-nova), also require it directly (`composer require a2zwebltd/brandgeo-laravel-client`) so Boost loads the skill.

## BrandGEO API resources

- [BrandGEO](https://brandgeo.co) — AI brand visibility audits & monitoring
- [API documentation](https://brandgeo.co/developers) — endpoints, auth, plan access, FAQ
- [Interactive API playground](https://brandgeo.co/developers/playground) — try every endpoint in the browser
- [OpenAPI 3.1 spec (YAML)](https://brandgeo.co/developers/openapi.yaml) — machine-readable contract (a copy ships in [`docs/api.openapi.yaml`](docs/api.openapi.yaml))
- [Get your API key](https://brandgeo.co/settings/api) — Settings → API in your dashboard
- [`a2zwebltd/brandgeo-laravel-nova`](https://github.com/a2zwebltd/brandgeo-laravel-nova) — BrandGEO dashboard inside Laravel Nova, built on this SDK

## Security Vulnerabilities

If you discover any security related issues, please email [biuro@a2zweb.co](mailto:biuro@a2zweb.co) instead of using the issue tracker.

## License

MIT. See [LICENSE](LICENSE.md).

## Credits

Developed and maintained by the **A2Z WEB** crew:
* [Dawid Makowski](https://github.com/makowskid)
* Website: [https://a2zweb.co/](https://a2zweb.co/)
* GitHub: [https://github.com/a2zwebltd/](https://github.com/a2zwebltd/)
