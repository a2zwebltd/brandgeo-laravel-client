<?php

namespace A2ZWeb\BrandGeoClient\Pagination;

use ArrayIterator;
use Closure;
use Countable;
use Illuminate\Support\LazyCollection;
use IteratorAggregate;
use Traversable;

/**
 * Page-based paginator. `foreach ($paginator)` iterates the fetched page only;
 * `foreach ($paginator->lazy())` opts into auto-pagination across all pages.
 *
 * @template T
 *
 * @implements IteratorAggregate<int, T>
 */
final class PagePaginator implements Countable, IteratorAggregate
{
    /**
     * @param  list<T>  $items
     * @param  Closure(int $page): self  $nextPageResolver
     */
    public function __construct(
        public readonly array $items,
        public readonly int $currentPage,
        public readonly int $perPage,
        public readonly int $total,
        public readonly int $lastPage,
        private readonly Closure $nextPageResolver,
    ) {}

    public function hasMorePages(): bool
    {
        return $this->currentPage < $this->lastPage;
    }

    /**
     * Fetch the next page (issues an API request), or null when exhausted.
     */
    public function nextPage(): ?self
    {
        if (! $this->hasMorePages()) {
            return null;
        }

        return ($this->nextPageResolver)($this->currentPage + 1);
    }

    /**
     * Lazily iterate every item across all remaining pages.
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
