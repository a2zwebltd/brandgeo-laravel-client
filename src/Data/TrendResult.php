<?php

namespace A2ZWeb\BrandGeoClient\Data;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, VisibilitySnapshot>
 */
final readonly class TrendResult implements Countable, IteratorAggregate
{
    public function __construct(
        /** @var list<VisibilitySnapshot> Overall rows, oldest first. */
        public array $points,
        /** The window actually applied — clamped to the plan's trend history. */
        public int $daysApplied,
        public int $daysMax,
    ) {}

    public static function fromResponse(array $json): self
    {
        return new self(
            points: array_map(VisibilitySnapshot::fromArray(...), $json['data'] ?? []),
            daysApplied: (int) ($json['meta']['days_applied'] ?? 0),
            daysMax: (int) ($json['meta']['days_max'] ?? 0),
        );
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->points);
    }

    public function count(): int
    {
        return count($this->points);
    }
}
