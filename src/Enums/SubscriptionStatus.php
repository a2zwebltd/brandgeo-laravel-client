<?php

namespace A2ZWeb\BrandGeoClient\Enums;

enum SubscriptionStatus: string
{
    case Trial = 'trial';
    case Active = 'active';
    case Free = 'free';
    case Expired = 'expired';
}
