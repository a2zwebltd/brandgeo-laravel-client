<?php

namespace A2ZWeb\BrandGeoClient\Enums;

use A2ZWeb\BrandGeoClient\Enums\Concerns\HasUnknownCase;

enum RecommendationPriority: string
{
    use HasUnknownCase;

    case P0 = 'P0';
    case P1 = 'P1';
    case P2 = 'P2';

    /** The API sent a value this client version doesn't know yet — upgrade the package to get its real case. */
    case Unknown = 'unknown';
}
