<?php

namespace A2ZWeb\BrandGeoClient\Data;

use A2ZWeb\BrandGeoClient\Enums\PromptCategory;
use A2ZWeb\BrandGeoClient\Support\Dates;
use Carbon\CarbonImmutable;

final readonly class PromptTemplate
{
    public function __construct(
        public int $id,
        public PromptCategory $category,
        /** Prompt text with `{brand}` / `{competitor}` / `{industry}` placeholders. */
        public string $template,
        public ?string $language,
        public bool $isCustom,
        public bool $isActive,
        public ?CarbonImmutable $createdAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            category: PromptCategory::from($data['category']),
            template: $data['template'],
            language: $data['language'] ?? null,
            isCustom: (bool) ($data['is_custom'] ?? false),
            isActive: (bool) ($data['is_active'] ?? false),
            createdAt: Dates::parse($data['created_at'] ?? null),
        );
    }
}
