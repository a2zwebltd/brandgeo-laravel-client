<?php

namespace A2ZWeb\BrandGeoClient\Data;

use A2ZWeb\BrandGeoClient\Enums\AuditStatus;
use A2ZWeb\BrandGeoClient\Support\Dates;
use Carbon\CarbonImmutable;

final readonly class LatestAuditSummary
{
    public function __construct(
        public string $uuid,
        public AuditStatus $status,
        public ?float $overallScore,
        public ?CarbonImmutable $createdAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'],
            status: AuditStatus::fromApi($data['status']),
            overallScore: isset($data['overall_score']) ? (float) $data['overall_score'] : null,
            createdAt: Dates::parse($data['created_at'] ?? null),
        );
    }
}
