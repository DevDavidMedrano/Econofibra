<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Coverage;

use App\Domain\Coverage\Entities\CoverageZone;
use App\Domain\Coverage\Entities\ZoneMatch;
use App\Domain\Coverage\Services\ZoneLocator;
use App\Domain\Coverage\ValueObjects\GeoPoint;
use App\Domain\Coverage\ValueObjects\PrecisionMargin;
use App\Domain\Coverage\ValueObjects\ZoneBoundary;
use PHPUnit\Framework\TestCase;

final class ZoneLocatorTest extends TestCase
{
    public function test_a_point_inside_a_single_zone_matches_it(): void
    {
        $fiber = $this->zone(1, 'Ejemplo Fibra', 20.0, -100.0);
        $antenna = $this->zone(2, 'Ejemplo Antena', 20.0, -99.95);

        $match = $this->locate(20.0, -100.0, 30.0, [$fiber, $antenna]);

        $this->assertTrue($match->isSingle());
        $this->assertFalse($match->isNone());
        $this->assertFalse($match->isAmbiguous());
        $this->assertSame($fiber, $match->zone());
    }

    public function test_a_point_on_the_border_of_a_zone_matches_it(): void
    {
        $zone = $this->zone(1, 'Ejemplo Fibra', 20.0, -100.0);

        $match = $this->locate(20.0, -99.995, 30.0, [$zone]);

        $this->assertTrue($match->isSingle());
        $this->assertSame($zone, $match->zone());
    }

    public function test_a_point_far_from_every_zone_matches_none(): void
    {
        $match = $this->locate(21.0, -101.0, 30.0, [$this->zone(1, 'Ejemplo Fibra', 20.0, -100.0)]);

        $this->assertTrue($match->isNone());
        $this->assertFalse($match->isSingle());
        $this->assertFalse($match->isAmbiguous());
        $this->assertNull($match->zone());
    }

    public function test_without_zones_it_matches_none(): void
    {
        $this->assertTrue($this->locate(20.0, -100.0, 30.0, [])->isNone());
    }

    public function test_a_point_in_overlapping_zones_is_ambiguous(): void
    {
        $match = $this->locate(20.051, -99.9, 10.0, [
            $this->zone(1, 'Ejemplo Norte', 20.05, -99.9),
            $this->zone(2, 'Ejemplo Sur', 20.053, -99.9),
        ]);

        $this->assertTrue($match->isAmbiguous());
        $this->assertFalse($match->isSingle());
        $this->assertFalse($match->isNone());
        $this->assertNull($match->zone());
    }

    public function test_a_point_on_a_border_shared_by_two_zones_is_ambiguous(): void
    {
        $match = $this->locate(20.0, -99.995, 10.0, [
            $this->zone(1, 'Oeste', 20.0, -100.0),
            $this->zone(2, 'Este', 20.0, -99.99),
        ]);

        $this->assertTrue($match->isAmbiguous());
    }

    public function test_a_margin_that_reaches_another_zone_is_ambiguous(): void
    {
        // The point is inside Oeste and about 73 m from Este.
        $oeste = $this->zone(1, 'Oeste', 20.0, -100.0);
        $zones = [$oeste, $this->zone(2, 'Este', 20.0, -99.9895)];

        $this->assertSame($oeste, $this->locate(20.0, -99.9952, 60.0, $zones)->zone());

        $match = $this->locate(20.0, -99.9952, 80.0, $zones);

        $this->assertTrue($match->isAmbiguous());
        $this->assertNull($match->zone());
    }

    public function test_a_point_without_zone_whose_margin_reaches_one_is_ambiguous(): void
    {
        $match = $this->locate(20.0, -99.9945, 60.0, [$this->zone(1, 'Ejemplo Fibra', 20.0, -100.0)]);

        $this->assertTrue($match->isAmbiguous());
        $this->assertNull($match->zone());
    }

    public function test_a_point_without_zone_whose_margin_falls_short_matches_none(): void
    {
        $match = $this->locate(20.0, -99.9945, 40.0, [$this->zone(1, 'Ejemplo Fibra', 20.0, -100.0)]);

        $this->assertTrue($match->isNone());
    }

    public function test_a_margin_equal_to_the_distance_reaches_the_zone(): void
    {
        $zone = $this->zone(1, 'Ejemplo Fibra', 20.0, -100.0);
        $point = new GeoPoint(20.0, -99.9945);
        $distance = $zone->boundary->distanceInMetersTo($point);
        $locator = new ZoneLocator;

        $this->assertTrue($locator->locate($point, new PrecisionMargin($distance), [$zone])->isAmbiguous());
    }

    public function test_a_point_in_a_zone_whose_margin_only_reaches_land_without_zone_matches_it(): void
    {
        $zone = $this->zone(1, 'Ejemplo Fibra', 20.0, -100.0);

        // About 10 m from the east border: the margin leaves the zone and reaches nothing else.
        $match = $this->locate(20.0, -99.9951, 100.0, [$zone, $this->zone(2, 'Ejemplo Antena', 20.0, -99.95)]);

        $this->assertTrue($match->isSingle());
        $this->assertSame($zone, $match->zone());
    }

    public function test_an_absent_margin_reaches_no_other_zone(): void
    {
        $zone = $this->zone(1, 'Ejemplo Fibra', 20.0, -100.0);
        $locator = new ZoneLocator;

        $this->assertTrue($locator->locate(new GeoPoint(20.0, -99.9945), new PrecisionMargin(null), [$zone])->isNone());
        $this->assertSame($zone, $locator->locate(new GeoPoint(20.0, -100.0), new PrecisionMargin(null), [$zone])->zone());
    }

    /** @param list<CoverageZone> $zones */
    private function locate(float $latitude, float $longitude, float $margin, array $zones): ZoneMatch
    {
        return (new ZoneLocator)->locate(new GeoPoint($latitude, $longitude), new PrecisionMargin($margin), $zones);
    }

    private function zone(int $id, string $name, float $latitude, float $longitude): CoverageZone
    {
        return new CoverageZone($id, $name, new ZoneBoundary([[
            new GeoPoint($latitude - 0.005, $longitude - 0.005),
            new GeoPoint($latitude - 0.005, $longitude + 0.005),
            new GeoPoint($latitude + 0.005, $longitude + 0.005),
            new GeoPoint($latitude + 0.005, $longitude - 0.005),
        ]]));
    }
}
