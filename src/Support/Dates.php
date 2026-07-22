<?php

namespace A2ZWeb\BrandGeoClient\Support;

use Carbon\CarbonImmutable;

final class Dates
{
    public static function parse(?string $value): ?CarbonImmutable
    {
        return $value === null ? null : CarbonImmutable::parse($value);
    }
}
