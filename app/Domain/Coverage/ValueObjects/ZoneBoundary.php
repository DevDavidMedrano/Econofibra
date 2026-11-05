<?php

declare(strict_types=1);

namespace App\Domain\Coverage\ValueObjects;

use InvalidArgumentException;

/**
 * One or more polygons without holes that do not cross the antimeridian.
 */
final readonly class ZoneBoundary
{
    private const EARTH_RADIUS_METERS = 6371008.8;

    /** Absorbs floating point rounding, so a point on a side counts as being on it. */
    private const BORDER_TOLERANCE_METERS = 0.001;

    /** @param list<list<GeoPoint>> $polygons */
    public function __construct(private array $polygons)
    {
        if ($polygons === []) {
            throw new InvalidArgumentException('A zone boundary needs at least one polygon.');
        }

        foreach ($polygons as $polygon) {
            if (count($polygon) < 3) {
                throw new InvalidArgumentException('A polygon needs at least three vertices.');
            }
        }
    }

    public function contains(GeoPoint $point): bool
    {
        if ($this->distanceInMetersTo($point) <= self::BORDER_TOLERANCE_METERS) {
            return true;
        }

        foreach ($this->polygons as $polygon) {
            if ($this->polygonEncloses($polygon, $point)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Shortest distance from the point to the sides of the polygons, also from inside them.
     */
    public function distanceInMetersTo(GeoPoint $point): float
    {
        $distance = INF;

        foreach ($this->polygons as $polygon) {
            foreach ($this->sides($polygon) as [$start, $end]) {
                $distance = min($distance, $this->distanceToSide($point, $start, $end));
            }
        }

        return $distance;
    }

    /** @param list<GeoPoint> $polygon */
    private function polygonEncloses(array $polygon, GeoPoint $point): bool
    {
        $inside = false;

        foreach ($this->sides($polygon) as [$start, $end]) {
            $crossesLatitude = ($start->latitude > $point->latitude) !== ($end->latitude > $point->latitude);

            if (! $crossesLatitude) {
                continue;
            }

            $progress = ($point->latitude - $start->latitude) / ($end->latitude - $start->latitude);
            $crossingLongitude = $start->longitude + $progress * ($end->longitude - $start->longitude);

            if ($point->longitude < $crossingLongitude) {
                $inside = ! $inside;
            }
        }

        return $inside;
    }

    /**
     * @param  list<GeoPoint>  $polygon
     * @return list<array{GeoPoint, GeoPoint}>
     */
    private function sides(array $polygon): array
    {
        $sides = [];
        $previous = $polygon[count($polygon) - 1];

        foreach ($polygon as $vertex) {
            $sides[] = [$previous, $vertex];
            $previous = $vertex;
        }

        return $sides;
    }

    /** Works on a flat projection centered on the point, which sits at the origin. */
    private function distanceToSide(GeoPoint $point, GeoPoint $start, GeoPoint $end): float
    {
        [$startX, $startY] = $this->project($start, $point);
        [$endX, $endY] = $this->project($end, $point);

        $sideX = $endX - $startX;
        $sideY = $endY - $startY;
        $squaredLength = $sideX ** 2 + $sideY ** 2;

        $progress = $squaredLength > 0.0
            ? max(0.0, min(1.0, -($startX * $sideX + $startY * $sideY) / $squaredLength))
            : 0.0;

        return hypot($startX + $progress * $sideX, $startY + $progress * $sideY);
    }

    /** @return array{float, float} */
    private function project(GeoPoint $vertex, GeoPoint $center): array
    {
        return [
            deg2rad($vertex->longitude - $center->longitude) * cos(deg2rad($center->latitude)) * self::EARTH_RADIUS_METERS,
            deg2rad($vertex->latitude - $center->latitude) * self::EARTH_RADIUS_METERS,
        ];
    }
}
