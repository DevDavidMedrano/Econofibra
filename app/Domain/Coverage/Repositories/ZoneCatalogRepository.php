<?php

declare(strict_types=1);

namespace App\Domain\Coverage\Repositories;

use App\Domain\Coverage\Entities\ImportedZone;
use App\Domain\Coverage\Entities\ZoneCatalogItem;
use App\Domain\Coverage\Entities\ZoneImportResult;
use App\Domain\Coverage\ValueObjects\TechnologyStatus;
use App\Domain\Coverage\ValueObjects\TechnologyType;
use App\Domain\Coverage\ValueObjects\ZoneName;
use App\Domain\Shared\AdminChangeRejected;

/**
 * The zones as the panel manages them. Every change is applied whole or not at all.
 */
interface ZoneCatalogRepository
{
    /** @return list<ZoneCatalogItem> */
    public function all(): array;

    /**
     * Creates the zones whose name is new and replaces the boundary of the others.
     *
     * @param  list<ImportedZone>  $zones
     *
     * @throws AdminChangeRejected
     */
    public function import(array $zones): ZoneImportResult;

    /** @throws AdminChangeRejected When the zone is missing or another one uses the name. */
    public function rename(int $zoneId, ZoneName $name): void;

    /**
     * Removes the zone with its technology statuses and prices.
     *
     * @throws AdminChangeRejected
     */
    public function delete(int $zoneId): void;

    /** @throws AdminChangeRejected */
    public function setTechnologyStatus(int $zoneId, TechnologyType $technology, TechnologyStatus $status): void;
}
