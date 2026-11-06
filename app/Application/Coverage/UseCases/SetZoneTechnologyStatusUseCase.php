<?php

declare(strict_types=1);

namespace App\Application\Coverage\UseCases;

use App\Domain\Coverage\Repositories\ZoneCatalogRepository;
use App\Domain\Coverage\ValueObjects\TechnologyStatus;
use App\Domain\Coverage\ValueObjects\TechnologyType;
use App\Domain\Shared\AdminChangeRejected;

final class SetZoneTechnologyStatusUseCase
{
    public function __construct(private ZoneCatalogRepository $zones) {}

    /** @throws AdminChangeRejected */
    public function execute(int $zoneId, TechnologyType $technology, TechnologyStatus $status): void
    {
        $this->zones->setTechnologyStatus($zoneId, $technology, $status);
    }
}
