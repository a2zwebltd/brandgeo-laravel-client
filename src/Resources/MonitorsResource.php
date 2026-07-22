<?php

namespace A2ZWeb\BrandGeoClient\Resources;

use A2ZWeb\BrandGeoClient\Data\Competitor;
use A2ZWeb\BrandGeoClient\Data\Monitor;
use A2ZWeb\BrandGeoClient\Data\PromptRun;
use A2ZWeb\BrandGeoClient\Data\PromptTemplate;
use A2ZWeb\BrandGeoClient\Data\TrendResult;
use A2ZWeb\BrandGeoClient\Data\VisibilitySnapshot;
use A2ZWeb\BrandGeoClient\Enums\MonitorStatus;
use A2ZWeb\BrandGeoClient\Enums\Provider;
use A2ZWeb\BrandGeoClient\Pagination\CursorPaginator;
use A2ZWeb\BrandGeoClient\Pagination\PagePaginator;
use DateTimeInterface;

class MonitorsResource extends Resource
{
    /** Pass to snapshots() to include per-engine rows alongside the overall ones. */
    public const PROVIDER_ALL = 'all';

    /**
     * @param  string|null  $brand  filter by brand uuid
     * @return PagePaginator<Monitor>
     */
    public function list(
        ?string $brand = null,
        ?MonitorStatus $status = null,
        int $page = 1,
        int $perPage = 25,
    ): PagePaginator {
        $json = $this->client->get('monitors', [
            'brand' => $brand,
            'status' => $status,
            'page' => $page,
            'per_page' => $perPage,
        ]);

        return $this->pagePaginate(
            $json,
            Monitor::fromArray(...),
            fn (int $nextPage) => $this->list($brand, $status, $nextPage, $perPage),
        );
    }

    /**
     * One monitor with its latest overall snapshot and (gated) recommendations.
     */
    public function get(string $uuid): Monitor
    {
        return Monitor::fromArray($this->client->get("monitors/{$uuid}")['data']);
    }

    /**
     * @return PagePaginator<Competitor>
     */
    public function competitors(string $uuid, int $page = 1, int $perPage = 25): PagePaginator
    {
        $json = $this->client->get("monitors/{$uuid}/competitors", [
            'page' => $page,
            'per_page' => $perPage,
        ]);

        return $this->pagePaginate(
            $json,
            Competitor::fromArray(...),
            fn (int $nextPage) => $this->competitors($uuid, $nextPage, $perPage),
        );
    }

    /**
     * All templates for the monitor — standard and custom queries (is_custom flag).
     *
     * @return PagePaginator<PromptTemplate>
     */
    public function promptTemplates(
        string $uuid,
        ?bool $isActive = null,
        int $page = 1,
        int $perPage = 25,
    ): PagePaginator {
        $json = $this->client->get("monitors/{$uuid}/prompt-templates", [
            'is_active' => $isActive,
            'page' => $page,
            'per_page' => $perPage,
        ]);

        return $this->pagePaginate(
            $json,
            PromptTemplate::fromArray(...),
            fn (int $nextPage) => $this->promptTemplates($uuid, $isActive, $nextPage, $perPage),
        );
    }

    /**
     * Prompt runs, newest first (cursor-paginated).
     *
     * @return CursorPaginator<PromptRun>
     */
    public function runs(
        string $uuid,
        ?Provider $provider = null,
        ?bool $brandMentioned = null,
        ?int $template = null,
        DateTimeInterface|string|null $from = null,
        DateTimeInterface|string|null $to = null,
        ?string $cursor = null,
        int $perPage = 50,
    ): CursorPaginator {
        $json = $this->client->get("monitors/{$uuid}/runs", [
            'provider' => $provider,
            'brand_mentioned' => $brandMentioned,
            'template' => $template,
            'from' => $this->formatDate($from),
            'to' => $this->formatDate($to),
            'cursor' => $cursor,
            'per_page' => $perPage,
        ]);

        return $this->cursorPaginate(
            $json,
            PromptRun::fromArray(...),
            fn (string $nextCursor) => $this->runs(
                $uuid, $provider, $brandMentioned, $template, $from, $to, $nextCursor, $perPage,
            ),
        );
    }

    /**
     * Visibility snapshots, newest first (cursor-paginated), capped at the plan's
     * trend-history window. Defaults to the overall (cross-engine) rows only —
     * pass a Provider for one engine, or self::PROVIDER_ALL for everything.
     *
     * @return CursorPaginator<VisibilitySnapshot>
     */
    public function snapshots(
        string $uuid,
        Provider|string|null $provider = null,
        ?string $cursor = null,
        int $perPage = 50,
    ): CursorPaginator {
        $json = $this->client->get("monitors/{$uuid}/snapshots", [
            'provider' => $provider,
            'cursor' => $cursor,
            'per_page' => $perPage,
        ]);

        return $this->cursorPaginate(
            $json,
            VisibilitySnapshot::fromArray(...),
            fn (string $nextCursor) => $this->snapshots($uuid, $provider, $nextCursor, $perPage),
        );
    }

    /**
     * Overall visibility trend, oldest first. `days` is clamped server-side to
     * the plan's window — check TrendResult::$daysApplied for the actual value.
     */
    public function trend(string $uuid, int $days = 30): TrendResult
    {
        return TrendResult::fromResponse(
            $this->client->get("monitors/{$uuid}/trend", ['days' => $days]),
        );
    }

    private function formatDate(DateTimeInterface|string|null $date): ?string
    {
        return $date instanceof DateTimeInterface ? $date->format('Y-m-d') : $date;
    }
}
