<?php

namespace A2ZWeb\BrandGeoClient\Data;

use A2ZWeb\BrandGeoClient\Support\Dates;
use Carbon\CarbonImmutable;

final readonly class Usage
{
    public function __construct(
        public int $brands,
        public int $monitors,
        public int $auditsThisMonth,
        /** Null = unlimited (internal accounts). */
        public ?int $auditsRemaining,
        public ?CarbonImmutable $auditQuotaResetsAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            brands: (int) ($data['brands'] ?? 0),
            monitors: (int) ($data['monitors'] ?? 0),
            auditsThisMonth: (int) ($data['audits_this_month'] ?? 0),
            auditsRemaining: $data['audits_remaining'] ?? null,
            auditQuotaResetsAt: Dates::parse($data['audit_quota_resets_at'] ?? null),
        );
    }
}
