<?php

namespace A2ZWeb\BrandGeoClient\Data;

final readonly class SentimentBreakdown
{
    public function __construct(
        public ?int $positive,
        public ?int $neutral,
        public ?int $negative,
        public ?float $netScore,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            positive: $data['positive'] ?? null,
            neutral: $data['neutral'] ?? null,
            negative: $data['negative'] ?? null,
            netScore: isset($data['net_score']) ? (float) $data['net_score'] : null,
        );
    }
}
