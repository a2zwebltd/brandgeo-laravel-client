<?php

namespace A2ZWeb\BrandGeoClient\Enums;

enum ReportStatus: string
{
    case Queued = 'queued';
    case Processing = 'processing';
    case Done = 'done';
    case Failed = 'failed';

    /** Trial paywall — the engine exists but its data is not included in the plan. */
    case Locked = 'locked';
}
