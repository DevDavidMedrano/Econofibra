<?php

declare(strict_types=1);

namespace App\Domain\Coverage\Entities;

use App\Domain\Coverage\ValueObjects\TechnologyStatus;

final readonly class ZoneCatalogItem
{
    /**
     * @param  array<string, TechnologyStatus>  $statuses  By technology code, with every technology.
     * @param  list<string>  $sampleStatuses  Technology codes whose stored status is sample data.
     */
    public function __construct(
        public int $id,
        public string $name,
        public array $statuses,
        public bool $isSample,
        public array $sampleStatuses,
    ) {}
}
