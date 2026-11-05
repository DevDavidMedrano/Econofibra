<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Coverage;

use App\Domain\Coverage\ValueObjects\GeoPoint;
use App\Domain\Coverage\ValueObjects\ZoneBoundary;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ZoneBoundaryTest extends TestCase
{
    #[DataProvider('pointsInsideTheSquare')]
    public function test_it_contains_points_inside_or_on_the_border(float $latitude, float $longitude): void
    {
        $this->assertTrue($this->square(20.0, -100.0)->contains(new GeoPoint($latitude, $longitude)));
    }

    /** @return array<string, array{float, float}> */
    public static function pointsInsideTheSquare(): array
    {
        return [
            'center' => [20.0, -100.0],
            'south border' => [19.995, -100.0],
            'north border' => [20.005, -100.0],
            'west border' => [20.0, -100.005],
            'east border' => [20.0, -99.995],
            'south west vertex' => [19.995, -100.005],
            'north east vertex' => [20.005, -99.995],
        ];
    }

    #[DataProvider('pointsOutsideTheSquare')]
    public function test_it_does_not_contain_points_outside(float $latitude, float $longitude): void
    {
        $this->assertFalse($this->square(20.0, -100.0)->contains(new GeoPoint($latitude, $longitude)));
    }

    /** @return array<string, array{float, float}> */
    public static function pointsOutsideTheSquare(): array
    {
        return [
            'north' => [20.006, -100.0],
            'east' => [20.0, -99.994],
            'west at the height of a vertex' => [20.005, -100.01],
            'east at the height of a vertex' => [19.995, -99.99],
            'far away' => [21.0, -101.0],
        ];
    }

    public function test_it_contains_a_point_on_a_diagonal_border(): void
    {
        $triangle = new ZoneBoundary([[
            new GeoPoint(20.0, -100.0),
            new GeoPoint(20.01, -99.97),
            new GeoPoint(20.03, -100.0),
        ]]);

        $this->assertTrue($triangle->contains(new GeoPoint(20.0 + 0.01 / 3, -99.99)));
    }

    public function test_it_respects_the_notch_of_a_concave_polygon(): void
    {
        $horseshoe = new ZoneBoundary([[
            new GeoPoint(20.00, -100.00),
            new GeoPoint(20.00, -99.97),
            new GeoPoint(20.03, -99.97),
            new GeoPoint(20.03, -99.98),
            new GeoPoint(20.01, -99.98),
            new GeoPoint(20.01, -99.99),
            new GeoPoint(20.03, -99.99),
            new GeoPoint(20.03, -100.00),
        ]]);

        $this->assertTrue($horseshoe->contains(new GeoPoint(20.02, -99.995)));
        $this->assertTrue($horseshoe->contains(new GeoPoint(20.02, -99.975)));
        $this->assertFalse($horseshoe->contains(new GeoPoint(20.02, -99.985)));
    }

    public function test_a_zone_with_several_polygons_contains_a_point_of_any_of_them(): void
    {
        $boundary = new ZoneBoundary([
            $this->squareRing(20.0, -100.0),
            $this->squareRing(20.0, -99.9),
        ]);

        $this->assertTrue($boundary->contains(new GeoPoint(20.0, -100.0)));
        $this->assertTrue($boundary->contains(new GeoPoint(20.0, -99.9)));
        $this->assertFalse($boundary->contains(new GeoPoint(20.0, -99.95)));
    }

    public function test_a_ring_closed_with_its_first_vertex_behaves_the_same(): void
    {
        $ring = $this->squareRing(20.0, -100.0);
        $boundary = new ZoneBoundary([[...$ring, $ring[0]]]);

        $this->assertTrue($boundary->contains(new GeoPoint(20.0, -100.0)));
        $this->assertFalse($boundary->contains(new GeoPoint(20.0, -99.994)));
        $this->assertEqualsWithDelta(
            $this->square(20.0, -100.0)->distanceInMetersTo(new GeoPoint(20.0, -99.994)),
            $boundary->distanceInMetersTo(new GeoPoint(20.0, -99.994)),
            0.000001,
        );
    }

    public function test_it_measures_the_distance_to_the_nearest_side(): void
    {
        // 0.0005 degrees of longitude at latitude 20, as in the plan's sample point.
        $distance = $this->square(20.0, -100.0)->distanceInMetersTo(new GeoPoint(20.0, -99.9945));

        $this->assertEqualsWithDelta(52.24, $distance, 0.05);
    }

    public function test_it_measures_the_distance_along_a_meridian(): void
    {
        // 0.001 degrees of latitude: 6 371 008.8 * pi / 180 / 1000 meters.
        $distance = $this->square(20.0, -100.0)->distanceInMetersTo(new GeoPoint(20.006, -100.0));

        $this->assertEqualsWithDelta(111.195, $distance, 0.01);
    }

    public function test_it_measures_the_distance_to_a_vertex_when_it_is_the_nearest_part(): void
    {
        $distance = $this->square(20.0, -100.0)->distanceInMetersTo(new GeoPoint(20.006, -99.994));

        $this->assertEqualsWithDelta(hypot(111.195, 111.195 * cos(deg2rad(20.006))), $distance, 0.01);
    }

    public function test_the_distance_is_zero_on_the_border(): void
    {
        $this->assertEqualsWithDelta(
            0.0,
            $this->square(20.0, -100.0)->distanceInMetersTo(new GeoPoint(20.005, -100.0)),
            0.000001,
        );
    }

    public function test_the_distance_uses_the_nearest_polygon(): void
    {
        $boundary = new ZoneBoundary([
            $this->squareRing(20.0, -100.0),
            $this->squareRing(20.0, -99.9),
        ]);

        $this->assertEqualsWithDelta(52.24, $boundary->distanceInMetersTo(new GeoPoint(20.0, -99.9055)), 0.05);
    }

    public function test_it_requires_at_least_one_polygon(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ZoneBoundary([]);
    }

    public function test_it_requires_three_vertices_per_polygon(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ZoneBoundary([[new GeoPoint(20.0, -100.0), new GeoPoint(20.1, -100.0)]]);
    }

    private function square(float $latitude, float $longitude): ZoneBoundary
    {
        return new ZoneBoundary([$this->squareRing($latitude, $longitude)]);
    }

    /** @return list<GeoPoint> */
    private function squareRing(float $latitude, float $longitude): array
    {
        return [
            new GeoPoint($latitude - 0.005, $longitude - 0.005),
            new GeoPoint($latitude - 0.005, $longitude + 0.005),
            new GeoPoint($latitude + 0.005, $longitude + 0.005),
            new GeoPoint($latitude + 0.005, $longitude - 0.005),
        ];
    }
}
