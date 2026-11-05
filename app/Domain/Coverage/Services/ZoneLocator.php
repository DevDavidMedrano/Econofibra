<?php

declare(strict_types=1);

namespace App\Domain\Coverage\Services;

use App\Domain\Coverage\Entities\CoverageZone;
use App\Domain\Coverage\Entities\ZoneMatch;
use App\Domain\Coverage\ValueObjects\GeoPoint;
use App\Domain\Coverage\ValueObjects\PrecisionMargin;

final class ZoneLocator
{
    /**
     * An absent margin reaches nothing beyond the point. It never gets here in a
     * real query, because low precision is resolved first (RF-13).
     *
     * @param  list<CoverageZone>  $zones
     */
    public function locate(GeoPoint $point, PrecisionMargin $margin, array $zones): ZoneMatch
    {
        $reach = $margin->meters ?? 0.0;
        $containing = [];

        foreach ($zones as $zone) {
            if ($zone->boundary->contains($point)) {
                $containing[] = $zone;
            } elseif ($zone->boundary->distanceInMetersTo($point) <= $reach) {
                return ZoneMatch::ambiguous();
            }
        }

        return match (count($containing)) {
            0 => ZoneMatch::none(),
            1 => ZoneMatch::single($containing[0]),
            default => ZoneMatch::ambiguous(),
        };
    }
}
