<?php

namespace A2ZWeb\BrandGeoClient\Exceptions;

/**
 * 402 — the account's trial expired (or subscription lapsed); detail endpoints
 * are paywalled while /account and list endpoints remain available.
 */
class SubscriptionRequiredException extends BrandGeoException {}
