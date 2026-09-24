---
name: brandgeo-sdk
description: Use when calling the BrandGEO public API from Laravel through a2zwebltd/brandgeo-laravel-client, i.e. the BrandGeo facade or an injected BrandGeoClient (account, brands, audits, monitors, runs, snapshots, trend), PagePaginator/CursorPaginator, BrandGeoException subclasses, config/brandgeo-client.php, BRANDGEO_* env keys, or faking the API with Http::fake in tests.
---

# BrandGEO Laravel Client

## When to use this skill

- Reading BrandGEO account, brand, audit or monitor data from a Laravel app.
- Switching between several BrandGEO accounts (agency apps).
- Walking paginated lists, handling API errors, rate limits and trial paywalls.
- Writing tests that fake BrandGEO responses.

## Install / wiring checklist

1. `composer require a2zwebltd/brandgeo-laravel-client`. The service provider and the `BrandGeo` alias are auto-discovered.
2. `.env`: `BRANDGEO_API_KEY=1|xxxx` (sent verbatim as a Bearer token). Optional: `BRANDGEO_BASE_URL` (default `https://brandgeo.co/api/v1`), `BRANDGEO_TIMEOUT` (30), `BRANDGEO_VERIFY_SSL` (true), `BRANDGEO_RETRY_TIMES` (0), `BRANDGEO_RETRY_SLEEP` (200 ms).
3. Optional: `php artisan vendor:publish --tag=brandgeo-client-config` publishes `config/brandgeo-client.php`.
4. A missing key only fails at request time, with `MissingApiKeyException`.

## API & config reference

### Entry points

```php
use A2ZWeb\BrandGeoClient\BrandGeoClient;
use A2ZWeb\BrandGeoClient\Facades\BrandGeo;

BrandGeo::brands()->list();                       // facade

public function __construct(private BrandGeoClient $brandGeo) {}   // DI, same singleton
```

`BrandGeoClient` is a container singleton built once from config. Changing `config('brandgeo-client.api_key')` at runtime does NOT affect an already-resolved client. Use the immutable clones instead; the singleton is never mutated:

```php
$client = BrandGeo::withApiKey($customer->brandgeo_api_key);   // returns a new BrandGeoClient
$client = BrandGeo::withBaseUrl('https://brandgeo.test/api/v1');
```

### Resources (all return typed readonly DTOs from `A2ZWeb\BrandGeoClient\Data`)

| Call | Returns |
| --- | --- |
| `account()->get()` | `Account` (`subscription`, `quota`, `usage`) |
| `brands()->list(int $page = 1, int $perPage = 25)` | `PagePaginator<Brand>` |
| `brands()->get(string $uuid)` | `Brand` (`latestAudit`, `monitor` summaries) |
| `audits()->list(?string $brand = null, ?AuditStatus $status = null, int $page = 1, int $perPage = 25)` | `PagePaginator<Audit>` |
| `audits()->get(string $uuid)` | `Audit` with `reports` and `recommendations` |
| `audits()->reports(string $uuid)` | `list<AuditReport>` (cheap progress polling) |
| `monitors()->list(?string $brand = null, ?MonitorStatus $status = null, int $page = 1, int $perPage = 25)` | `PagePaginator<Monitor>` |
| `monitors()->get(string $uuid)` | `Monitor` with `latestSnapshot` and `recommendations` |
| `monitors()->competitors(string $uuid, int $page = 1, int $perPage = 25)` | `PagePaginator<Competitor>` |
| `monitors()->promptTemplates(string $uuid, ?bool $isActive = null, int $page = 1, int $perPage = 25)` | `PagePaginator<PromptTemplate>` |
| `monitors()->runs(string $uuid, ?Provider $provider = null, ?bool $brandMentioned = null, ?int $template = null, DateTimeInterface\|string\|null $from = null, $to = null, ?string $cursor = null, int $perPage = 50)` | `CursorPaginator<PromptRun>` |
| `monitors()->snapshots(string $uuid, Provider\|string\|null $provider = null, ?string $cursor = null, int $perPage = 50)` | `CursorPaginator<VisibilitySnapshot>` |
| `monitors()->trend(string $uuid, int $days = 30)` | `TrendResult` (iterable `points`, `daysApplied`, `daysMax`) |

`$brand` filters take the brand **uuid**. Null filters are dropped; enums are sent as their value, booleans as `1`/`0`, dates as `Y-m-d`.

Enums (`A2ZWeb\BrandGeoClient\Enums`): `AuditStatus` (Queued, Processing, Done, Failed), `MonitorStatus` (Active, Paused, Archived), `Provider` (Openai, Anthropic, Gemini, Xai, Deepseek), `AuditMode` (Trained, WebSearch), `ReportStatus` (Queued, Processing, Done, Failed, Locked), `PromptCategory`, `RecommendationPriority` (P0–P2), `SubscriptionStatus` (Trial, Active, Free, Expired).

`snapshots()` without a provider returns only the overall (cross-engine) rows, where `$snapshot->provider === null` / `isOverall()`. Pass a `Provider` for one engine or `MonitorsResource::PROVIDER_ALL` for everything.

### Score scales

- `Audit::$overallScore`, `LatestAuditSummary::$overallScore`, `AuditReport::$normalizedScore`, `VisibilitySnapshot::$visibilityScore`: **0–100**.
- `Recommendations::$overallScore`: **0–10** (mapped from `overall_score_0_10`). Multiply by 10 before comparing or charting next to the others.

### Pagination

- `PagePaginator` (brands, audits, monitors, competitors, prompt templates): `items`, `currentPage`, `perPage`, `total`, `lastPage`, `hasMorePages()`, `nextPage()`.
- `CursorPaginator` (runs, snapshots): `items`, `perPage`, `nextCursor`, `prevCursor`, `hasMorePages()`, `nextPage()`.
- `foreach ($paginator)` / `count()` cover the **fetched page only**. `nextPage()` issues a new request and returns null when exhausted. `lazy()` returns a `LazyCollection` that walks every remaining page, keeping the original filters and `perPage`.

### Exceptions (`A2ZWeb\BrandGeoClient\Exceptions`, all extend `BrandGeoException` with `$status` and `$body`)

| Exception | When |
| --- | --- |
| `MissingApiKeyException` | no key configured (thrown before any request) |
| `AuthenticationException` | 401, invalid or revoked key |
| `SubscriptionRequiredException` | 402, trial expired or subscription lapsed; detail endpoints paywalled, `account()` and list endpoints still work |
| `NotFoundException` | 404, missing **or owned by another account** (indistinguishable by design) |
| `ValidationException` | 422, `$errors` is `field => list<string>` |
| `RateLimitException` | 429, `$retryAfter` (seconds), `$limit`, `$remaining` |
| `ApiException` | 5xx and any other status |

Connection failures are NOT wrapped: they surface as `Illuminate\Http\Client\ConnectionException`.

### Retries

`BRANDGEO_RETRY_TIMES` > 0 enables retries for connection errors, 5xx and 429 only; other 4xx fail on the first attempt. The value is the **total** number of attempts (Laravel `retry()` semantics), so `1` means no retry and `3` means up to two retries. `BRANDGEO_RETRY_SLEEP` is a fixed delay in ms and does not honour `Retry-After`.

## Recipes

Agency loop over customer accounts:

```php
use A2ZWeb\BrandGeoClient\Data\Brand;

foreach (Customer::whereNotNull('brandgeo_api_key')->cursor() as $customer) {
    $client = BrandGeo::withApiKey($customer->brandgeo_api_key);

    $client->brands()->list(perPage: 100)->lazy()->each(function (Brand $brand) use ($customer) {
        // $brand->latestAudit?->overallScore is 0–100
    });
}
```

Cache data, never paginators (they hold a resolver closure, so they cannot be serialized):

```php
$brands = Cache::remember("brandgeo:{$accountId}:brands", 600,
    fn () => BrandGeo::brands()->list(perPage: 100)->items);   // cache ->items, or ->lazy()->all()
```

Trial-safe audit rendering:

```php
use A2ZWeb\BrandGeoClient\Exceptions\NotFoundException;
use A2ZWeb\BrandGeoClient\Exceptions\SubscriptionRequiredException;

try {
    $audit = BrandGeo::audits()->get($uuid);
} catch (SubscriptionRequiredException) {
    return back()->with('error', 'Your BrandGEO trial has ended.');
} catch (NotFoundException) {
    abort(404);   // also covers "belongs to another account"
}

foreach ($audit->reports ?? [] as $report) {
    if ($report->isLocked()) {
        continue;   // trial paywall: only uuid/provider/mode/status are set
    }
    $report->normalizedScore;   // 0–100
}

if ($audit->recommendations?->isPreview()) {
    $audit->recommendations->lockedActions;   // items hidden behind the paywall
}
```

## Gotchas

- `Audit::$reports` and `Audit::$recommendations` are null on `audits()->list()`; they are only populated by `audits()->get()`. Same for `Monitor::$latestSnapshot` / `$recommendations`.
- Locked reports (`ReportStatus::Locked`, `isLocked()`) carry no score, result or findings. Don't treat them as failed.
- `trend($uuid, days: 365)` is clamped server-side to the plan's history. Read `$trend->daysApplied`, not your input.
- Unknown enum values from the API throw PHP's `ValueError` during hydration, because DTOs use `Enum::from()` (except `VisibilitySnapshot::$provider` and `ActionItem::$priority`, which use `tryFrom()` and become null). If BrandGEO adds a new provider or status before you upgrade the client, the whole call fails. Catch `ValueError` around calls that must not break and keep the package updated.
- `withApiKey()` / `withBaseUrl()` return a new client. Calling them and discarding the result changes nothing.

## Testing

The client uses Laravel's HTTP factory, so `Http::fake()` intercepts every call. Patterns match the full URL; `?page=` is part of it:

```php
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

config(['brandgeo-client.api_key' => '1|test-key']);   // before the client is first resolved

Http::fake([
    'brandgeo.co/api/v1/account' => Http::response(['data' => [/* account payload */]]),
    'brandgeo.co/api/v1/brands?page=1*' => Http::response(['data' => [], 'meta' => ['current_page' => 1, 'last_page' => 1]]),
    'brandgeo.co/api/v1/audits/*' => Http::response(['message' => 'Not found.'], 404),
]);

Http::assertSent(fn (Request $r) => $r->hasHeader('Authorization', 'Bearer 1|test-key'));
```

- Error mapping: 401 reads `error.message`, other statuses read `message`, 422 reads `errors`, 429 reads the `Retry-After`, `X-RateLimit-Limit` and `X-RateLimit-Remaining` headers.
- Keep `BRANDGEO_RETRY_TIMES` at 0 in tests, or fakes for 5xx/429 are hit several times.
- Response shapes: `docs/api.openapi.yaml` in the package (the OpenAPI 3.1 contract).
