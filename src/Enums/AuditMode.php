<?php

namespace A2ZWeb\BrandGeoClient\Enums;

enum AuditMode: string
{
    case Trained = 'trained';
    case WebSearch = 'web_search';
}
