<?php

namespace A2ZWeb\BrandGeoClient\Data;

use A2ZWeb\BrandGeoClient\Enums\MonitorStatus;
use A2ZWeb\BrandGeoClient\Support\Dates;
use Carbon\CarbonImmutable;

final readonly class Monitor
{
    public function __construct(
        public string $uuid,
        public MonitorStatus $status,
        public ?string $industry,
        public ?BrandSummary $brand,
        public string $brandName,
        public string $brandUrl,
        public ?string $trackingFrequency,
        public ?CarbonImmutable $lastRunAt,
        public ?CarbonImmutable $createdAt,
        /** Null on list responses; the latest overall snapshot on get(). */
        public ?VisibilitySnapshot $latestSnapshot,
        /** Null on list responses and when not generated yet. */
        public ?Recommendations $recommendations,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'],
            status: MonitorStatus::from($data['status']),
            industry: $data['industry'] ?? null,
            brand: isset($data['brand']) ? BrandSummary::fromArray($data['brand']) : null,
            brandName: $data['brand_name'],
            brandUrl: $data['brand_url'],
            trackingFrequency: $data['tracking_frequency'] ?? null,
            lastRunAt: Dates::parse($data['last_run_at'] ?? null),
            createdAt: Dates::parse($data['created_at'] ?? null),
            latestSnapshot: isset($data['latest_snapshot']) ? VisibilitySnapshot::fromArray($data['latest_snapshot']) : null,
            recommendations: isset($data['recommendations']) ? Recommendations::fromArray($data['recommendations']) : null,
        );
    }
}
