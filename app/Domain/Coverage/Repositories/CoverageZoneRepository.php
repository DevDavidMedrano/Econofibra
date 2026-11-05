<?php

declare(strict_types=1);

namespace App\Domain\Coverage\Repositories;

use App\Domain\Coverage\Entities\CoverageZone;

interface CoverageZoneRepository
{
    /** @return list<CoverageZone> */
    public function all(): array;
}
