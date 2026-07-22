<?php

namespace A2ZWeb\BrandGeoClient\Exceptions;

/**
 * 404 — the resource does not exist OR belongs to another account;
 * the API deliberately makes those indistinguishable.
 */
class NotFoundException extends BrandGeoException {}
