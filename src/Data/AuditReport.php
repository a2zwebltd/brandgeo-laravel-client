<?php

namespace A2ZWeb\BrandGeoClient\Data;

use A2ZWeb\BrandGeoClient\Enums\AuditMode;
use A2ZWeb\BrandGeoClient\Enums\Provider;
use A2ZWeb\BrandGeoClient\Enums\ReportStatus;
use A2ZWeb\BrandGeoClient\Support\Dates;
use Carbon\CarbonImmutable;

final readonly class AuditReport
{
    public function __construct(
        public string $uuid,
        public Provider $provider,
        public AuditMode $mode,
        public ReportStatus $status,
        public ?string $model,
        public ?float $normalizedScore,
        public ?string $grade,
        /** Per-section scoring, `scoring_summary` and `analysis` blocks. */
        public ?array $result,
        public ?array $sources,
        public ?array $findings,
        /** Sanitized error description — present only when the report failed. */
        public ?string $error,
        public ?CarbonImmutable $processedAt,
    ) {}

    /** Trial paywall stub — no data beyond the engine's existence. */
    public function isLocked(): bool
    {
        return $this->status === ReportStatus::Locked;
    }

    public function isFailed(): bool
    {
        return $this->status === ReportStatus::Failed;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'],
            provider: Provider::from($data['provider']),
            mode: AuditMode::from($data['mode']),
            status: ReportStatus::from($data['status']),
            model: $data['model'] ?? null,
            normalizedScore: isset($data['normalized_score']) ? (float) $data['normalized_score'] : null,
            grade: $data['grade'] ?? null,
            result: $data['result'] ?? null,
            sources: $data['sources'] ?? null,
            findings: $data['findings'] ?? null,
            error: $data['error'] ?? null,
            processedAt: Dates::parse($data['processed_at'] ?? null),
        );
    }
}
