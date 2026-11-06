<?php

declare(strict_types=1);

namespace App\Application\Plans\UseCases;

use App\Domain\Plans\Entities\PlanCatalogItem;
use App\Domain\Plans\Repositories\PlanCatalogRepository;

final class ListPlansUseCase
{
    public function __construct(private PlanCatalogRepository $plans) {}

    /** @return list<PlanCatalogItem> */
    public function execute(): array
    {
        return $this->plans->all();
    }
}
