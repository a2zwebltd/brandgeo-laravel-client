<?php

namespace A2ZWeb\BrandGeoClient\Enums;

enum Provider: string
{
    case Openai = 'openai';
    case Anthropic = 'anthropic';
    case Gemini = 'gemini';
    case Xai = 'xai';
    case Deepseek = 'deepseek';
}
