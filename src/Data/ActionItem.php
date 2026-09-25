<?php

namespace A2ZWeb\BrandGeoClient\Data;

use A2ZWeb\BrandGeoClient\Enums\RecommendationPriority;

final readonly class ActionItem
{
    public function __construct(
        public ?string $title,
        public ?string $why,
        public ?string $horizon,
        public ?RecommendationPriority $priority,
        public ?string $effort,
        public ?string $impact,
        public ?string $how,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? null,
            why: $data['why'] ?? null,
            horizon: $data['horizon'] ?? null,
            priority: isset($data['priority']) ? RecommendationPriority::fromApi($data['priority']) : null,
            effort: $data['effort'] ?? null,
            impact: $data['impact'] ?? null,
            how: $data['how'] ?? null,
        );
    }
}
