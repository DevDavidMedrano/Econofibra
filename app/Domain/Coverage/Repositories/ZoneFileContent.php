<?php

declare(strict_types=1);

namespace App\Domain\Coverage\Repositories;

final readonly class ZoneFileContent
{
    /** @param list<ZoneFileEntry> $entries */
    public function __construct(
        public array $entries,
        public int $ignoredGeometries,
    ) {}
}
