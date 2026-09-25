<?php

namespace A2ZWeb\BrandGeoClient\Data;

use A2ZWeb\BrandGeoClient\Enums\Provider;
use A2ZWeb\BrandGeoClient\Support\Dates;
use Carbon\CarbonImmutable;

final readonly class PromptRun
{
    public function __construct(
        public string $uuid,
        public Provider $provider,
        public ?int $promptTemplateId,
        public string $prompt,
        public ?string $response,
        public ?bool $brandMentioned,
        /** 1-based position of the brand in the answer, when mentioned. */
        public ?int $brandPosition,
        public ?array $competitorMentions,
        public ?string $sentiment,
        public ?float $sentimentScore,
        public ?array $citations,
        public ?CarbonImmutable $executedAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'],
            provider: Provider::fromApi($data['provider']),
            promptTemplateId: $data['prompt_template_id'] ?? null,
            prompt: $data['prompt'],
            response: $data['response'] ?? null,
            brandMentioned: $data['brand_mentioned'] ?? null,
            brandPosition: $data['brand_position'] ?? null,
            competitorMentions: $data['competitor_mentions'] ?? null,
            sentiment: $data['sentiment'] ?? null,
            sentimentScore: isset($data['sentiment_score']) ? (float) $data['sentiment_score'] : null,
            citations: $data['citations'] ?? null,
            executedAt: Dates::parse($data['executed_at'] ?? null),
        );
    }
}
