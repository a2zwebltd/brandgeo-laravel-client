<?php

namespace A2ZWeb\BrandGeoClient\Enums;

enum MonitorStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Archived = 'archived';
}
