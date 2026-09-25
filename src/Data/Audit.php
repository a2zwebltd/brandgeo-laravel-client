<?php

namespace A2ZWeb\BrandGeoClient\Data;

use A2ZWeb\BrandGeoClient\Enums\AuditStatus;
use A2ZWeb\BrandGeoClient\Support\Dates;
use Carbon\CarbonImmutable;

final readonly class Audit
{
    public function __construct(
        public string $uuid,
        public AuditStatus $status,
        public ?BrandSummary $brand,
        public string $brandName,
        public string $brandUrl,
        /** Average trained-mode score across done engines, 0–100. */
        public ?float $overallScore,
        public ?string $recommendationsStatus,
        /** Public read-only report URL — null unless the owner shared the audit. */
        public ?string $shareUrl,
        public ?CarbonImmutable $createdAt,
        public ?CarbonImmutable $updatedAt,
        /** @var list<AuditReport>|null Null on list responses; populated on get(). */
        public ?array $reports,
        /** Null on list responses and when not generated yet. */
        public ?Recommendations $recommendations,
    ) {}

    public function isComplete(): bool
    {
        return $this->status === AuditStatus::Done;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'],
            status: AuditStatus::fromApi($data['status']),
            brand: isset($data['brand']) ? BrandSummary::fromArray($data['brand']) : null,
            brandName: $data['brand_name'],
            brandUrl: $data['brand_url'],
            overallScore: isset($data['overall_score']) ? (float) $data['overall_score'] : null,
            recommendationsStatus: $data['recommendations_status'] ?? null,
            shareUrl: $data['share_url'] ?? null,
            createdAt: Dates::parse($data['created_at'] ?? null),
            updatedAt: Dates::parse($data['updated_at'] ?? null),
            reports: isset($data['reports']) ? array_map(AuditReport::fromArray(...), $data['reports']) : null,
            recommendations: isset($data['recommendations']) ? Recommendations::fromArray($data['recommendations']) : null,
        );
    }
}
