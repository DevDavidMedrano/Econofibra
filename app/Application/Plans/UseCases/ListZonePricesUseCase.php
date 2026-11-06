<?php

declare(strict_types=1);

namespace App\Application\Plans\UseCases;

use App\Domain\Plans\Entities\ZonePriceSheet;
use App\Domain\Plans\Repositories\PlanCatalogRepository;

final class ListZonePricesUseCase
{
    public function __construct(private PlanCatalogRepository $plans) {}

    /** Null when the zone does not exist. */
    public function execute(int $zoneId): ?ZonePriceSheet
    {
        return $this->plans->zonePrices($zoneId);
    }
}
