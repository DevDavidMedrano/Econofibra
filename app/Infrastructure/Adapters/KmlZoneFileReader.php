<?php

declare(strict_types=1);

namespace App\Infrastructure\Adapters;

use App\Domain\Coverage\Repositories\ZoneFileContent;
use App\Domain\Coverage\Repositories\ZoneFileEntry;
use App\Domain\Coverage\Repositories\ZoneFileReader;
use App\Domain\Coverage\Repositories\ZoneFileRejected;
use DOMDocument;
use DOMNode;
use DOMXPath;

/**
 * Reads an untrusted KML. Entities are never expanded and nothing outside the file is loaded:
 * only Placemark and Polygon elements are visited, so NetworkLink and href are never read.
 * Elements are matched by local name to accept any KML version.
 */
final readonly class KmlZoneFileReader implements ZoneFileReader
{
    private const UNREADABLE = 'El archivo no es un KML legible.';

    private const IGNORED_GEOMETRIES = ".//*[local-name()='Point' or local-name()='LineString'"
        ." or (local-name()='LinearRing' and not(ancestor::*[local-name()='Polygon']))]";

    private const OUTER_RING = "./*[local-name()='outerBoundaryIs']/*[local-name()='LinearRing']/*[local-name()='coordinates']";

    public function __construct(
        private int $maxKilobytes,
        private int $maxFileVertices,
    ) {}

    public function read(string $contents): ZoneFileContent
    {
        if (str_starts_with($contents, 'PK')) {
            throw new ZoneFileRejected('El archivo es un KMZ: exporta el mapa como KML e inténtalo de nuevo.');
        }

        if (strlen($contents) > $this->maxKilobytes * 1024) {
            throw new ZoneFileRejected(sprintf('El archivo pesa más de %g MB.', $this->maxKilobytes / 1024));
        }

        $xpath = new DOMXPath($this->parse($contents));
        $entries = [];
        $ignored = 0;
        $vertices = 0;

        foreach ($xpath->query("//*[local-name()='Placemark']") as $placemark) {
            $ignored += $xpath->query(self::IGNORED_GEOMETRIES, $placemark)->length;
            $name = trim((string) $xpath->evaluate("string(./*[local-name()='name'][1])", $placemark));
            $polygons = [];

            foreach ($xpath->query(".//*[local-name()='Polygon']", $placemark) as $polygon) {
                $polygons[] = $this->readPolygon($xpath, $polygon, $name);
                $vertices += count($polygons[array_key_last($polygons)]);

                if ($vertices > $this->maxFileVertices) {
                    throw new ZoneFileRejected("El archivo tiene más de {$this->maxFileVertices} vértices.");
                }
            }

            if ($polygons !== []) {
                $entries[] = new ZoneFileEntry($name, $polygons);
            }
        }

        if ($entries === []) {
            throw new ZoneFileRejected('El archivo no contiene polígonos.');
        }

        return new ZoneFileContent($entries, $ignored);
    }

    private function parse(string $contents): DOMDocument
    {
        if ($contents === '') {
            throw new ZoneFileRejected(self::UNREADABLE);
        }

        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);

        try {
            // Without LIBXML_NOENT or LIBXML_DTDLOAD, so neither entities nor a DTD are processed.
            $loaded = $document->loadXML($contents, LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        if (! $loaded) {
            throw new ZoneFileRejected(self::UNREADABLE);
        }

        // Checked on the parsed document, because searching the bytes would miss UTF-16.
        if ($document->doctype !== null) {
            throw new ZoneFileRejected('El archivo contiene DOCTYPE o entidades, que no se permiten.');
        }

        if ($document->documentElement?->localName !== 'kml') {
            throw new ZoneFileRejected(self::UNREADABLE);
        }

        return $document;
    }

    /** @return list<array{float, float}> */
    private function readPolygon(DOMXPath $xpath, DOMNode $polygon, string $name): array
    {
        if ($xpath->query("./*[local-name()='innerBoundaryIs']", $polygon)->length > 0) {
            throw new ZoneFileRejected('Un polígono tiene huecos, que no se permiten.', $name);
        }

        $rings = $xpath->query(self::OUTER_RING, $polygon);

        if ($rings->length !== 1) {
            throw new ZoneFileRejected('Un polígono debe tener un solo contorno exterior.', $name);
        }

        $tuples = preg_split('/\s+/', $rings->item(0)->textContent, -1, PREG_SPLIT_NO_EMPTY);
        $vertices = array_map(fn (string $tuple): array => $this->toVertex($tuple, $name), $tuples);

        if (count($vertices) > 1 && $vertices[0] === $vertices[count($vertices) - 1]) {
            array_pop($vertices);
        }

        return $vertices;
    }

    /**
     * KML writes longitude,latitude[,height]; the domain works with [latitude, longitude].
     *
     * @return array{float, float}
     */
    private function toVertex(string $tuple, string $name): array
    {
        $values = explode(',', $tuple);

        if (! in_array(count($values), [2, 3], true) || array_filter($values, is_numeric(...)) !== $values) {
            throw new ZoneFileRejected('Un polígono tiene coordenadas ilegibles.', $name);
        }

        return [(float) $values[1], (float) $values[0]];
    }
}
