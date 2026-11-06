<?php

declare(strict_types=1);

namespace App\Domain\Coverage\Repositories;

/**
 * An element of a zone file, as it was read: neither its name nor its polygons are checked yet.
 */
final readonly class ZoneFileEntry
{
    /** @param list<list<array{float, float}>> $polygons Each vertex is [latitude, longitude]. */
    public function __construct(
        public string $name,
        public array $polygons,
    ) {}
}
