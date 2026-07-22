<?php

namespace A2ZWeb\BrandGeoClient\Data;

use A2ZWeb\BrandGeoClient\Support\Dates;
use Carbon\CarbonImmutable;

final readonly class Account
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?CarbonImmutable $createdAt,
        public Subscription $subscription,
        public Quota $quota,
        public Usage $usage,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            email: $data['email'],
            createdAt: Dates::parse($data['created_at'] ?? null),
            subscription: Subscription::fromArray($data['subscription']),
            quota: Quota::fromArray($data['quota']),
            usage: Usage::fromArray($data['usage']),
        );
    }
}
