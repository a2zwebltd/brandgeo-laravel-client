<?php

namespace A2ZWeb\BrandGeoClient\Enums;

enum AuditStatus: string
{
    case Queued = 'queued';
    case Processing = 'processing';
    case Done = 'done';
    case Failed = 'failed';
}
