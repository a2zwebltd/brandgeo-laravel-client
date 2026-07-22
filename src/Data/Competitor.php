<?php

namespace A2ZWeb\BrandGeoClient\Data;

use A2ZWeb\BrandGeoClient\Support\Dates;
use Carbon\CarbonImmutable;

final readonly class Competitor
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $url,
        public ?CarbonImmutable $createdAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            url: $data['url'] ?? null,
            createdAt: Dates::parse($data['created_at'] ?? null),
        );
    }
}
