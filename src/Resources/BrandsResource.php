<?php

namespace A2ZWeb\BrandGeoClient\Resources;

use A2ZWeb\BrandGeoClient\Data\Brand;
use A2ZWeb\BrandGeoClient\Pagination\PagePaginator;

class BrandsResource extends Resource
{
    /**
     * @return PagePaginator<Brand>
     */
    public function list(int $page = 1, int $perPage = 25): PagePaginator
    {
        $json = $this->client->get('brands', ['page' => $page, 'per_page' => $perPage]);

        return $this->pagePaginate(
            $json,
            Brand::fromArray(...),
            fn (int $nextPage) => $this->list($nextPage, $perPage),
        );
    }

    public function get(string $uuid): Brand
    {
        return Brand::fromArray($this->client->get("brands/{$uuid}")['data']);
    }
}
