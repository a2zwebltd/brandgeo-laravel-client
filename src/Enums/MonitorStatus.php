<?php

namespace A2ZWeb\BrandGeoClient\Enums;

use A2ZWeb\BrandGeoClient\Enums\Concerns\HasUnknownCase;

enum MonitorStatus: string
{
    use HasUnknownCase;

    case Active = 'active';
    case Paused = 'paused';
    case Archived = 'archived';

    /** The API sent a value this client version doesn't know yet — upgrade the package to get its real case. */
    case Unknown = 'unknown';
}
