<?php

namespace A2ZWeb\BrandGeoClient\Resources;

use A2ZWeb\BrandGeoClient\BrandGeoClient;
use A2ZWeb\BrandGeoClient\Pagination\CursorPaginator;
use A2ZWeb\BrandGeoClient\Pagination\PagePaginator;
use Closure;

abstract class Resource
{
    public function __construct(protected readonly BrandGeoClient $client) {}

    /**
     * @param  Closure(array): mixed  $map  item mapper (fromArray)
     * @param  Closure(int): PagePaginator  $resolver  fetches a given page with the same filters
     */
    protected function pagePaginate(array $json, Closure $map, Closure $resolver): PagePaginator
    {
        $meta = $json['meta'] ?? [];

        return new PagePaginator(
            items: array_map($map, $json['data'] ?? []),
            currentPage: (int) ($meta['current_page'] ?? 1),
            perPage: (int) ($meta['per_page'] ?? count($json['data'] ?? [])),
            total: (int) ($meta['total'] ?? count($json['data'] ?? [])),
            lastPage: (int) ($meta['last_page'] ?? 1),
            nextPageResolver: $resolver,
        );
    }

    /**
     * @param  Closure(array): mixed  $map  item mapper (fromArray)
     * @param  Closure(string): CursorPaginator  $resolver  fetches the page at a given cursor
     */
    protected function cursorPaginate(array $json, Closure $map, Closure $resolver): CursorPaginator
    {
        $meta = $json['meta'] ?? [];

        return new CursorPaginator(
            items: array_map($map, $json['data'] ?? []),
            perPage: (int) ($meta['per_page'] ?? count($json['data'] ?? [])),
            nextCursor: $meta['next_cursor'] ?? null,
            prevCursor: $meta['prev_cursor'] ?? null,
            nextPageResolver: $resolver,
        );
    }
}
