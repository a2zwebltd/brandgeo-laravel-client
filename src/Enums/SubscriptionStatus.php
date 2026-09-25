<?php

namespace A2ZWeb\BrandGeoClient\Enums;

use A2ZWeb\BrandGeoClient\Enums\Concerns\HasUnknownCase;

enum SubscriptionStatus: string
{
    use HasUnknownCase;

    case Trial = 'trial';
    case Active = 'active';
    case Free = 'free';
    case Expired = 'expired';

    /** The API sent a value this client version doesn't know yet — upgrade the package to get its real case. */
    case Unknown = 'unknown';
}
