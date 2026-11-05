<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Coverage\Entities\CoverageZone;
use App\Domain\Coverage\Repositories\CoverageZoneRepository;
use App\Domain\Coverage\ValueObjects\GeoPoint;
use App\Domain\Coverage\ValueObjects\ZoneBoundary;
use App\Infrastructure\Persistence\Eloquent\CoverageZoneModel;
use InvalidArgumentException;

/**
 * A boundary is stored as a JSON list of polygons, each a list of [latitude, longitude].
 * A malformed one throws, because skipping it would turn covered homes into Scenario D.
 */
final class EloquentCoverageZoneRepository implements CoverageZoneRepository
{
    public function all(): array
    {
        return CoverageZoneModel::query()
            ->orderBy('id')
            ->get()
            ->map(fn (CoverageZoneModel $zone): CoverageZone => new CoverageZone(
                $zone->id,
                $zone->name,
                $this->toBoundary($zone->boundary),
            ))
            ->all();
    }

    private function toBoundary(mixed $polygons): ZoneBoundary
    {
        if (! is_array($polygons) || ! array_is_list($polygons)) {
            throw new InvalidArgumentException('A stored boundary must be a list of polygons.');
        }

        return new ZoneBoundary(array_map($this->toPolygon(...), $polygons));
    }

    /** @return list<GeoPoint> */
    private function toPolygon(mixed $vertices): array
    {
        if (! is_array($vertices) || ! array_is_list($vertices)) {
            throw new InvalidArgumentException('A stored polygon must be a list of vertices.');
        }

        return array_map($this->toPoint(...), $vertices);
    }

    private function toPoint(mixed $vertex): GeoPoint
    {
        if (! is_array($vertex) || ! array_is_list($vertex) || count($vertex) !== 2) {
            throw new InvalidArgumentException('A stored vertex must be a latitude and longitude pair.');
        }

        [$latitude, $longitude] = $vertex;

        if (! (is_int($latitude) || is_float($latitude)) || ! (is_int($longitude) || is_float($longitude))) {
            throw new InvalidArgumentException('A stored vertex must hold two numbers.');
        }

        return new GeoPoint((float) $latitude, (float) $longitude);
    }
}
