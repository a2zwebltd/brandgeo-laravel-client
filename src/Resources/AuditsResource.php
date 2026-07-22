<?php

namespace A2ZWeb\BrandGeoClient\Resources;

use A2ZWeb\BrandGeoClient\Data\Audit;
use A2ZWeb\BrandGeoClient\Data\AuditReport;
use A2ZWeb\BrandGeoClient\Enums\AuditStatus;
use A2ZWeb\BrandGeoClient\Pagination\PagePaginator;

class AuditsResource extends Resource
{
    /**
     * @param  string|null  $brand  filter by brand uuid
     * @return PagePaginator<Audit>
     */
    public function list(
        ?string $brand = null,
        ?AuditStatus $status = null,
        int $page = 1,
        int $perPage = 25,
    ): PagePaginator {
        $json = $this->client->get('audits', [
            'brand' => $brand,
            'status' => $status,
            'page' => $page,
            'per_page' => $perPage,
        ]);

        return $this->pagePaginate(
            $json,
            Audit::fromArray(...),
            fn (int $nextPage) => $this->list($brand, $status, $nextPage, $perPage),
        );
    }

    /**
     * One audit with its per-engine reports and (gated) recommendations.
     */
    public function get(string $uuid): Audit
    {
        return Audit::fromArray($this->client->get("audits/{$uuid}")['data']);
    }

    /**
     * The audit's per-engine reports only — a cheap endpoint for polling
     * processing progress.
     *
     * @return list<AuditReport>
     */
    public function reports(string $uuid): array
    {
        return array_map(
            AuditReport::fromArray(...),
            $this->client->get("audits/{$uuid}/reports")['data'] ?? [],
        );
    }
}
