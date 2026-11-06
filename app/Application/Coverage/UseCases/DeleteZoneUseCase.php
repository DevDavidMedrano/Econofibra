<?php

declare(strict_types=1);

namespace App\Application\Coverage\UseCases;

use App\Domain\Coverage\Repositories\ZoneCatalogRepository;
use App\Domain\Shared\AdminChangeRejected;

final class DeleteZoneUseCase
{
    public function __construct(private ZoneCatalogRepository $zones) {}

    /** @throws AdminChangeRejected */
    public function execute(int $zoneId): void
    {
        $this->zones->delete($zoneId);
    }
}
