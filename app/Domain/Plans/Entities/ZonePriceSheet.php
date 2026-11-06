<?php

declare(strict_types=1);

namespace App\Domain\Plans\Entities;

final readonly class ZonePriceSheet
{
    /**
     * @param  list<ZonePriceItem>  $prices  The plans assigned to the zone.
     * @param  list<PlanCatalogItem>  $unassignedPlans  The plans that can still be assigned to it.
     */
    public function __construct(
        public int $zoneId,
        public string $zoneName,
        public bool $zoneIsSample,
        public array $prices,
        public array $unassignedPlans,
    ) {}
}
