<?php

namespace A2ZWeb\BrandGeoClient\Exceptions;

use Illuminate\Http\Client\Response;
use RuntimeException;

class BrandGeoException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?int $status = null,
        public readonly ?array $body = null,
    ) {
        parent::__construct($message, $status ?? 0);
    }

    /**
     * Map a failed API response to the matching exception subclass —
     * the single place error semantics live.
     */
    public static function fromResponse(Response $response): self
    {
        $status = $response->status();
        $body = $response->json() ?? [];

        return match ($status) {
            // The API's 401 uses a nonstandard nested shape: {"error": {"message": "..."}}
            401 => new AuthenticationException(
                $body['error']['message'] ?? $body['message'] ?? 'Unauthorized',
                $status,
                $body,
            ),
            402 => new SubscriptionRequiredException(
                $body['message'] ?? 'Subscription required.',
                $status,
                $body,
            ),
            404 => new NotFoundException(
                $body['message'] ?? 'Resource not found.',
                $status,
                $body,
            ),
            422 => new ValidationException(
                $body['message'] ?? 'Invalid request parameters.',
                $body['errors'] ?? [],
                $status,
                $body,
            ),
            429 => new RateLimitException(
                $body['message'] ?? 'Too many requests.',
                retryAfter: self::intHeader($response, 'Retry-After'),
                limit: self::intHeader($response, 'X-RateLimit-Limit'),
                remaining: self::intHeader($response, 'X-RateLimit-Remaining'),
                status: $status,
                body: $body,
            ),
            default => new ApiException(
                $body['message'] ?? "BrandGEO API request failed with status {$status}.",
                $status,
                $body,
            ),
        };
    }

    private static function intHeader(Response $response, string $header): ?int
    {
        $value = $response->header($header);

        return $value === '' || $value === null ? null : (int) $value;
    }
}
