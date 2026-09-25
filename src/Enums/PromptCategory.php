<?php

namespace A2ZWeb\BrandGeoClient\Enums;

use A2ZWeb\BrandGeoClient\Enums\Concerns\HasUnknownCase;

enum PromptCategory: string
{
    use HasUnknownCase;

    case Discovery = 'discovery';
    case Comparison = 'comparison';
    case Recommendation = 'recommendation';
    case Sentiment = 'sentiment';
    case Feature = 'feature';
    case UseCase = 'use_case';
    case Custom = 'custom';

    /** The API sent a value this client version doesn't know yet — upgrade the package to get its real case. */
    case Unknown = 'unknown';
}
