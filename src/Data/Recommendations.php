<?php

namespace A2ZWeb\BrandGeoClient\Data;

final readonly class Recommendations
{
    public function __construct(
        public bool $fullAccess,
        public ?float $overallScore,
        public ?string $executiveSummary,
        /** @var list<ActionItem> */
        public array $actionPlan,
        /** Number of action-plan items hidden behind the paywall (trial preview). */
        public int $lockedActions,
        /** The untouched `data` document — full-access responses carry extra sections here. */
        public array $raw,
    ) {}

    public function isPreview(): bool
    {
        return ! $this->fullAccess;
    }

    public static function fromArray(array $data): self
    {
        $doc = $data['data'] ?? [];

        return new self(
            fullAccess: (bool) ($data['full_access'] ?? false),
            overallScore: isset($doc['overall_score_0_10']) ? (float) $doc['overall_score_0_10'] : null,
            executiveSummary: $doc['executive_summary'] ?? null,
            actionPlan: array_map(ActionItem::fromArray(...), $doc['action_plan'] ?? []),
            lockedActions: (int) ($data['locked_actions'] ?? 0),
            raw: $doc,
        );
    }
}
