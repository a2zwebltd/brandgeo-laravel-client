<?php

namespace A2ZWeb\BrandGeoClient\Pagination;

use ArrayIterator;
use Closure;
use Countable;
use Illuminate\Support\LazyCollection;
use IteratorAggregate;
use Traversable;

/**
 * Cursor-based paginator (runs, snapshots). `foreach ($paginator)` iterates the
 * fetched page only; `foreach ($paginator->lazy())` follows next_cursor to the end.
 *
 * @template T
 *
 * @implements IteratorAggregate<int, T>
 */
final class CursorPaginator implements Countable, IteratorAggregate
{
    /**
     * @param  list<T>  $items
     * @param  Closure(string $cursor): self  $nextPageResolver
     */
    public function __construct(
        public readonly array $items,
        public readonly int $perPage,
        public readonly ?string $nextCursor,
        public readonly ?string $prevCursor,
        private readonly Closure $nextPageResolver,
    ) {}

    public function hasMorePages(): bool
    {
        return $this->nextCursor !== null;
    }

    /**
     * Fetch the next page (issues an API request), or null when exhausted.
     */
    public function nextPage(): ?self
    {
        if ($this->nextCursor === null) {
            return null;
        }

        return ($this->nextPageResolver)($this->nextCursor);
    }

    /**
     * Lazily iterate every item, following next_cursor until it runs out.
     *
     * @return LazyCollection<int, T>
     */
    public function lazy(): LazyCollection
    {
        return LazyCollection::make(function () {
            $page = $this;

            while ($page !== null) {
                // Plain yield (not `yield from`) so page keys don't collide.
                foreach ($page->items as $item) {
                    yield $item;
                }

                $page = $page->nextPage();
            }
        });
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }
}
