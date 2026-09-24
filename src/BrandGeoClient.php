<?php

namespace A2ZWeb\BrandGeoClient;

use A2ZWeb\BrandGeoClient\Exceptions\BrandGeoException;
use A2ZWeb\BrandGeoClient\Exceptions\MissingApiKeyException;
use A2ZWeb\BrandGeoClient\Resources\AccountResource;
use A2ZWeb\BrandGeoClient\Resources\AuditsResource;
use A2ZWeb\BrandGeoClient\Resources\BrandsResource;
use A2ZWeb\BrandGeoClient\Resources\MonitorsResource;
use BackedEnum;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Throwable;

final class BrandGeoClient
{
    public function __construct(
        private readonly Factory $http,
        private readonly ?string $apiKey,
        private readonly string $baseUrl = 'https://brandgeo.co/api/v1',
        private readonly int $timeout = 30,
        private readonly bool $verify = true,
        private readonly int $retryTimes = 0,
        private readonly int $retrySleep = 200,
    ) {}

    /**
     * Immutable clone with a different API key — safe under the container
     * singleton (agency apps switching between customer accounts).
     */
    public function withApiKey(string $apiKey): self
    {
        return new self(
            $this->http, $apiKey, $this->baseUrl,
            $this->timeout, $this->verify, $this->retryTimes, $this->retrySleep,
        );
    }

    public function withBaseUrl(string $baseUrl): self
    {
        return new self(
            $this->http, $this->apiKey, rtrim($baseUrl, '/'),
            $this->timeout, $this->verify, $this->retryTimes, $this->retrySleep,
        );
    }

    public function account(): AccountResource
    {
        return new AccountResource($this);
    }

    public function brands(): BrandsResource
    {
        return new BrandsResource($this);
    }

    public function audits(): AuditsResource
    {
        return new AuditsResource($this);
    }

    public function monitors(): MonitorsResource
    {
        return new MonitorsResource($this);
    }

    /**
     * Perform a GET request and return the decoded JSON body.
     *
     * @internal Used by the resource classes.
     *
     * @throws BrandGeoException
     */
    public function get(string $path, array $query = []): array
    {
        $response = $this->pendingRequest()->get($path, $this->normalizeQuery($query));

        if ($response->failed()) {
            throw BrandGeoException::fromResponse($response);
        }

        return $response->json() ?? [];
    }

    private function pendingRequest(): PendingRequest
    {
        if ($this->apiKey === null || $this->apiKey === '') {
            throw MissingApiKeyException::make();
        }

        $request = $this->http
            ->baseUrl($this->baseUrl)
            ->withToken($this->apiKey)
            ->acceptJson()
            ->timeout($this->timeout)
            ->withOptions(['verify' => $this->verify]);

        if ($this->retryTimes > 0) {
            $request = $request->retry(
                $this->retryTimes,
                $this->retrySleep,
                when: self::shouldRetry(...),
                throw: false,
            );
        }

        return $request;
    }

    /**
     * Retry only transient failures: connection errors, 5xx and 429. Other 4xx
     * (401, 402, 404, 422) are deterministic, so retrying them just burns rate limit.
     */
    private static function shouldRetry(Throwable $exception): bool
    {
        if ($exception instanceof ConnectionException) {
            return true;
        }

        return $exception instanceof RequestException
            && ($exception->response->serverError() || $exception->response->status() === 429);
    }

    /**
     * Drop null filters and serialize enums/booleans the way the API expects.
     */
    private function normalizeQuery(array $query): array
    {
        $normalized = [];

        foreach ($query as $key => $value) {
            if ($value === null) {
                continue;
            }

            $normalized[$key] = match (true) {
                $value instanceof BackedEnum => $value->value,
                is_bool($value) => $value ? '1' : '0',
                default => $value,
            };
        }

        return $normalized;
    }
}
