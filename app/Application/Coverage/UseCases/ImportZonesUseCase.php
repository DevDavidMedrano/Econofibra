<?php

declare(strict_types=1);

namespace App\Application\Coverage\UseCases;

use App\Application\Coverage\DTOs\ImportZonesOutput;
use App\Domain\Coverage\Entities\ImportedZone;
use App\Domain\Coverage\Repositories\ZoneCatalogRepository;
use App\Domain\Coverage\Repositories\ZoneFileEntry;
use App\Domain\Coverage\Repositories\ZoneFileReader;
use App\Domain\Coverage\Repositories\ZoneFileRejected;
use App\Domain\Coverage\Services\ZonePolygonPolicy;
use App\Domain\Coverage\ValueObjects\GeoPoint;
use App\Domain\Coverage\ValueObjects\ZoneBoundary;
use App\Domain\Coverage\ValueObjects\ZoneName;
use App\Domain\Shared\AdminChangeRejected;
use InvalidArgumentException;

final class ImportZonesUseCase
{
    public function __construct(
        private ZoneFileReader $reader,
        private ZoneCatalogRepository $zones,
        private ZonePolygonPolicy $polygons,
        private int $maxPolygonVertices,
    ) {}

    /** @throws AdminChangeRejected When anything in the file fails; no zone changes. */
    public function execute(string $contents): ImportZonesOutput
    {
        $content = $this->reader->read($contents);
        $zones = [];
        $seen = [];

        foreach ($content->entries as $entry) {
            $name = $this->nameOf($entry);

            if (isset($seen[$name->key()])) {
                throw new ZoneFileRejected('El archivo repite un nombre de zona.', $entry->name);
            }

            $seen[$name->key()] = true;
            $zones[] = new ImportedZone($name, $this->boundaryOf($entry));
        }

        $result = $this->zones->import($zones);

        return new ImportZonesOutput($result->created, $result->replaced, $content->ignoredGeometries);
    }

    private function nameOf(ZoneFileEntry $entry): ZoneName
    {
        try {
            return new ZoneName($entry->name);
        } catch (InvalidArgumentException $invalid) {
            throw new ZoneFileRejected('Hay un polígono sin un nombre válido. '.$invalid->getMessage(), $entry->name);
        }
    }

    private function boundaryOf(ZoneFileEntry $entry): ZoneBoundary
    {
        $polygons = [];

        foreach ($entry->polygons as $vertices) {
            $violation = $this->polygons->violation($vertices, $this->maxPolygonVertices);

            if ($violation !== null) {
                throw new ZoneFileRejected("Un polígono no es válido: {$violation}", $entry->name);
            }

            $polygons[] = array_map(
                static fn (array $vertex): GeoPoint => new GeoPoint($vertex[0], $vertex[1]),
                $vertices,
            );
        }

        return new ZoneBoundary($polygons);
    }
}
