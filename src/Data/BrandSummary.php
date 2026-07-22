<?php

namespace A2ZWeb\BrandGeoClient\Data;

final readonly class BrandSummary
{
    public function __construct(
        public string $uuid,
        public string $name,
        public ?string $url,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'],
            name: $data['name'],
            url: $data['url'] ?? null,
        );
    }
}
