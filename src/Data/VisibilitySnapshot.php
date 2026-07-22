<?php

namespace A2ZWeb\BrandGeoClient\Data;

use A2ZWeb\BrandGeoClient\Enums\Provider;
use A2ZWeb\BrandGeoClient\Support\Dates;
use Carbon\CarbonImmutable;

final readonly class VisibilitySnapshot
{
    public function __construct(
        public ?CarbonImmutable $date,
        /** Null = the overall (cross-engine aggregate) row. */
        public ?Provider $provider,
        public ?float $visibilityScore,
        public ?int $mentionCount,
        public ?int $totalPrompts,
        public ?float $avgPosition,
        public ?SentimentBreakdown $sentiment,
        public ?array $competitors,
        public ?array $topCitations,
    ) {}

    public function isOverall(): bool
    {
        return $this->provider === null;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            date: Dates::parse($data['date'] ?? null),
            provider: isset($data['provider']) ? Provider::tryFrom($data['provider']) : null,
            visibilityScore: isset($data['visibility_score']) ? (float) $data['visibility_score'] : null,
            mentionCount: $data['mention_count'] ?? null,
            totalPrompts: $data['total_prompts'] ?? null,
            avgPosition: isset($data['avg_position']) ? (float) $data['avg_position'] : null,
            sentiment: isset($data['sentiment']) ? SentimentBreakdown::fromArray($data['sentiment']) : null,
            competitors: $data['competitors'] ?? null,
            topCitations: $data['top_citations'] ?? null,
        );
    }
}
