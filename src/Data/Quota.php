<?php

namespace A2ZWeb\BrandGeoClient\Data;

/**
 * Plan limits. Null values mean "unlimited" (internal accounts).
 */
final readonly class Quota
{
    public function __construct(
        public string $planName,
        public ?int $brands,
        public ?int $auditsPerMonth,
        public ?int $competitorsPerBrand,
        public ?int $customQueriesPerBrand,
        public int $trendHistoryDays,
        public bool $whiteLabel,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            planName: $data['plan_name'],
            brands: $data['brands'] ?? null,
            auditsPerMonth: $data['audits_per_month'] ?? null,
            competitorsPerBrand: $data['competitors_per_brand'] ?? null,
            customQueriesPerBrand: $data['custom_queries_per_brand'] ?? null,
            trendHistoryDays: (int) $data['trend_history_days'],
            whiteLabel: (bool) ($data['white_label'] ?? false),
        );
    }
}
