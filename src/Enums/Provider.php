<?php

namespace A2ZWeb\BrandGeoClient\Enums;

use A2ZWeb\BrandGeoClient\Enums\Concerns\HasUnknownCase;

enum Provider: string
{
    use HasUnknownCase;

    case Openai = 'openai';
    case Anthropic = 'anthropic';
    case Gemini = 'gemini';
    case Xai = 'xai';
    case Deepseek = 'deepseek';

    /** The API sent a value this client version doesn't know yet — upgrade the package to get its real case. */
    case Unknown = 'unknown';
}
