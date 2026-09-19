<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface PlatformDataServiceInterface
{
    /**
     * Get hourly metrics for a given date and hour across all shops.
     */
    public function fetchHourlyMetrics(string $date, int $hour): Collection;

    /**
     * Get all platforms with their status and shops.
     */
    public function getPlatforms(): Collection;

    /**
     * Get all active shops, optionally filtered by platform.
     */
    public function getShops(?int $platformId = null): Collection;

    /**
     * Get platform summary metrics for a given date (and optionally hour).
     */
    public function getPlatformSummary(string $date, ?int $hour = null): Collection;

    /**
     * Get shop summary metrics for a given date (and optionally hour).
     */
    public function getShopSummary(string $date, ?int $hour = null): Collection;

    /**
     * Get hourly performance timeline for a given date.
     */
    public function getHourlyPerformance(string $date): Collection;

    /**
     * Get current vs previous hour comparison metrics.
     */
    public function getComparison(string $date, int $currentHour, int $previousHour): array;
}
