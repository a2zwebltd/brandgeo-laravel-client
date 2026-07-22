<?php

namespace A2ZWeb\BrandGeoClient\Exceptions;

/**
 * 429 — per-account rate limit exceeded (120/min paid, 30/min trial).
 */
class RateLimitException extends BrandGeoException
{
    public function __construct(
        string $message,
        /** Seconds until the window resets (Retry-After header). */
        public readonly ?int $retryAfter = null,
        public readonly ?int $limit = null,
        public readonly ?int $remaining = null,
        ?int $status = null,
        ?array $body = null,
    ) {
        parent::__construct($message, $status, $body);
    }
}
