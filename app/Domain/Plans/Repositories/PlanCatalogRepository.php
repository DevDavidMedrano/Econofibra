<?php

declare(strict_types=1);

namespace App\Domain\Plans\Repositories;

use App\Domain\Coverage\ValueObjects\TechnologyType;
use App\Domain\Plans\Entities\PlanCatalogItem;
use App\Domain\Plans\Entities\ZonePriceSheet;
use App\Domain\Plans\ValueObjects\Money;
use App\Domain\Plans\ValueObjects\PlanName;
use App\Domain\Plans\ValueObjects\PlanSpeed;
use App\Domain\Shared\AdminChangeRejected;

/**
 * The plans and zone prices as the panel manages them. Every change is applied whole or
 * not at all, and a change to a sample record keeps its mark.
 */
interface PlanCatalogRepository
{
    /** @return list<PlanCatalogItem> */
    public function all(): array;

    /** @throws AdminChangeRejected */
    public function create(TechnologyType $technology, PlanName $name, PlanSpeed $speed): void;

    /** @throws AdminChangeRejected When the plan is missing, or its technology changes while it has prices. */
    public function update(int $planId, TechnologyType $technology, PlanName $name, PlanSpeed $speed): void;

    /**
     * Removes the plan with its prices in every zone.
     *
     * @throws AdminChangeRejected
     */
    public function delete(int $planId): void;

    /** Null when the zone does not exist. */
    public function zonePrices(int $zoneId): ?ZonePriceSheet;

    /** @throws AdminChangeRejected When the zone or the plan is missing. */
    public function setZonePrice(int $zoneId, int $planId, Money $price, Money $installationCost): void;

    /** @throws AdminChangeRejected When the plan is not assigned to the zone. */
    public function removeZonePrice(int $zoneId, int $planId): void;
}
