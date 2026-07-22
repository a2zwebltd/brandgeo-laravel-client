<?php

namespace A2ZWeb\BrandGeoClient\Data;

use A2ZWeb\BrandGeoClient\Enums\SubscriptionStatus;

final readonly class Subscription
{
    public function __construct(
        public SubscriptionStatus $status,
        public ?string $plan,
        public bool $onTrial,
        public ?int $trialDaysRemaining,
        /** True for paid and internal accounts; false for trials. */
        public bool $hasFullAccess,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: SubscriptionStatus::from($data['status']),
            plan: $data['plan'] ?? null,
            onTrial: (bool) ($data['on_trial'] ?? false),
            trialDaysRemaining: $data['trial_days_remaining'] ?? null,
            hasFullAccess: (bool) ($data['has_full_access'] ?? false),
        );
    }
}
