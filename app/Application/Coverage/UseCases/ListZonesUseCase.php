<?php

declare(strict_types=1);

namespace App\Application\Coverage\UseCases;

use App\Domain\Coverage\Entities\ZoneCatalogItem;
use App\Domain\Coverage\Repositories\ZoneCatalogRepository;

final class ListZonesUseCase
{
    public function __construct(private ZoneCatalogRepository $zones) {}

    /** @return list<ZoneCatalogItem> */
    public function execute(): array
    {
        return $this->zones->all();
    }
}
