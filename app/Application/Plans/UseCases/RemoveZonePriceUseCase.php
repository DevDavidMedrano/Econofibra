<?php

declare(strict_types=1);

namespace App\Application\Plans\UseCases;

use App\Domain\Plans\Repositories\PlanCatalogRepository;
use App\Domain\Shared\AdminChangeRejected;

final class RemoveZonePriceUseCase
{
    public function __construct(private PlanCatalogRepository $plans) {}

    /** @throws AdminChangeRejected */
    public function execute(int $zoneId, int $planId): void
    {
        $this->plans->removeZonePrice($zoneId, $planId);
    }
}
