<?php

namespace A2ZWeb\BrandGeoClient\Data;

use A2ZWeb\BrandGeoClient\Support\Dates;
use Carbon\CarbonImmutable;

final readonly class Brand
{
    public function __construct(
        public string $uuid,
        public string $name,
        public string $url,
        public ?string $industry,
        public ?CarbonImmutable $createdAt,
        public ?CarbonImmutable $updatedAt,
        public ?LatestAuditSummary $latestAudit,
        public ?MonitorSummary $monitor,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'],
            name: $data['name'],
            url: $data['url'],
            industry: $data['industry'] ?? null,
            createdAt: Dates::parse($data['created_at'] ?? null),
            updatedAt: Dates::parse($data['updated_at'] ?? null),
            latestAudit: isset($data['latest_audit']) ? LatestAuditSummary::fromArray($data['latest_audit']) : null,
            monitor: isset($data['monitor']) ? MonitorSummary::fromArray($data['monitor']) : null,
        );
    }
}
