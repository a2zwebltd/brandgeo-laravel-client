<?php

namespace A2ZWeb\BrandGeoClient\Facades;

use A2ZWeb\BrandGeoClient\BrandGeoClient;
use A2ZWeb\BrandGeoClient\Resources\AccountResource;
use A2ZWeb\BrandGeoClient\Resources\AuditsResource;
use A2ZWeb\BrandGeoClient\Resources\BrandsResource;
use A2ZWeb\BrandGeoClient\Resources\MonitorsResource;
use Illuminate\Support\Facades\Facade;

/**
 * @method static AccountResource account()
 * @method static BrandsResource brands()
 * @method static AuditsResource audits()
 * @method static MonitorsResource monitors()
 * @method static BrandGeoClient withApiKey(string $apiKey)
 * @method static BrandGeoClient withBaseUrl(string $baseUrl)
 *
 * @see BrandGeoClient
 */
class BrandGeo extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BrandGeoClient::class;
    }
}
