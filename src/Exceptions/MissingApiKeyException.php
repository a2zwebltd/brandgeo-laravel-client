<?php

namespace A2ZWeb\BrandGeoClient\Exceptions;

class MissingApiKeyException extends BrandGeoException
{
    public static function make(): self
    {
        return new self(
            'No BrandGEO API key configured. Set BRANDGEO_API_KEY in your .env '
            .'(generate a key at Settings → API) or call withApiKey() on the client.'
        );
    }
}
