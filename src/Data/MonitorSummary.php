<?php

namespace A2ZWeb\BrandGeoClient\Data;

use A2ZWeb\BrandGeoClient\Enums\MonitorStatus;

final readonly class MonitorSummary
{
    public function __construct(
        public string $uuid,
        public MonitorStatus $status,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'],
            status: MonitorStatus::fromApi($data['status']),
        );
    }
}
