<?php

namespace A2ZWeb\BrandGeoClient\Exceptions;

/**
 * 422 — invalid query parameters.
 */
class ValidationException extends BrandGeoException
{
    public function __construct(
        string $message,
        /** @var array<string, list<string>> field => messages */
        public readonly array $errors = [],
        ?int $status = null,
        ?array $body = null,
    ) {
        parent::__construct($message, $status, $body);
    }
}
