<?php

namespace A2ZWeb\BrandGeoClient\Enums\Concerns;

/**
 * Forward compatibility for API enums: a value this client version doesn't
 * know yet hydrates to the `Unknown` case instead of throwing a ValueError.
 */
trait HasUnknownCase
{
    public static function fromApi(string $value): static
    {
        return static::tryFrom($value) ?? static::Unknown;
    }

    /**
     * Every case except `Unknown` — use this for UI lists and filters.
     *
     * @return list<static>
     */
    public static function known(): array
    {
        return array_values(array_filter(static::cases(), fn (self $case) => ! $case->isUnknown()));
    }

    public function isUnknown(): bool
    {
        return $this === static::Unknown;
    }
}
