<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Coverage;

use App\Domain\Coverage\ValueObjects\GeoPoint;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class GeoPointTest extends TestCase
{
    public function test_it_keeps_its_coordinates(): void
    {
        $point = new GeoPoint(20.5, -100.25);

        $this->assertSame(20.5, $point->latitude);
        $this->assertSame(-100.25, $point->longitude);
    }

    #[DataProvider('limitCoordinates')]
    public function test_it_accepts_the_limits_of_the_range(float $latitude, float $longitude): void
    {
        $point = new GeoPoint($latitude, $longitude);

        $this->assertSame($latitude, $point->latitude);
        $this->assertSame($longitude, $point->longitude);
    }

    /** @return array<string, array{float, float}> */
    public static function limitCoordinates(): array
    {
        return [
            'north and east' => [90.0, 180.0],
            'south and west' => [-90.0, -180.0],
            'origin' => [0.0, 0.0],
        ];
    }

    #[DataProvider('invalidCoordinates')]
    public function test_it_rejects_coordinates_out_of_range(float $latitude, float $longitude): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GeoPoint($latitude, $longitude);
    }

    /** @return array<string, array{float, float}> */
    public static function invalidCoordinates(): array
    {
        return [
            'latitude above 90' => [90.1, 0.0],
            'latitude below -90' => [-90.1, 0.0],
            'longitude above 180' => [0.0, 180.1],
            'longitude below -180' => [0.0, -180.1],
            'latitude not a number' => [NAN, 0.0],
            'longitude not a number' => [0.0, NAN],
            'infinite latitude' => [INF, 0.0],
            'infinite longitude' => [0.0, -INF],
        ];
    }
}
