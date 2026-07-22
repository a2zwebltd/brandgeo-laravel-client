<?php

use A2ZWeb\BrandGeoClient\Tests\TestCase;

pest()->extend(TestCase::class)->in('Feature');

/**
 * Load a JSON fixture from tests/Fixtures.
 */
function apiFixture(string $name): array
{
    return json_decode(
        file_get_contents(__DIR__."/Fixtures/{$name}.json"),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );
}
