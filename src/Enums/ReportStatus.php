<?php

namespace A2ZWeb\BrandGeoClient\Enums;

use A2ZWeb\BrandGeoClient\Enums\Concerns\HasUnknownCase;

enum ReportStatus: string
{
    use HasUnknownCase;

    case Queued = 'queued';
    case Processing = 'processing';
    case Done = 'done';
    case Failed = 'failed';

    /** Trial paywall — the engine exists but its data is not included in the plan. */
    case Locked = 'locked';

    /** The API sent a value this client version doesn't know yet — upgrade the package to get its real case. */
    case Unknown = 'unknown';
}
