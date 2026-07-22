<?php

namespace A2ZWeb\BrandGeoClient\Enums;

enum PromptCategory: string
{
    case Discovery = 'discovery';
    case Comparison = 'comparison';
    case Recommendation = 'recommendation';
    case Sentiment = 'sentiment';
    case Feature = 'feature';
    case UseCase = 'use_case';
    case Custom = 'custom';
}
