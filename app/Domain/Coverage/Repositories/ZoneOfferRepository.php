<?php

declare(strict_types=1);

namespace App\Domain\Coverage\Repositories;

use App\Domain\Coverage\Entities\TechnologyOffer;

interface ZoneOfferRepository
{
    /**
     * One offer per known technology, with the plans priced in the zone.
     * A technology without a status in the zone comes as unavailable.
     *
     * @return list<TechnologyOffer>
     */
    public function forZone(int $zoneId): array;
}
