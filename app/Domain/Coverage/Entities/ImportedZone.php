<?php

declare(strict_types=1);

namespace App\Domain\Coverage\Entities;

use App\Domain\Coverage\ValueObjects\ZoneBoundary;
use App\Domain\Coverage\ValueObjects\ZoneName;

final readonly class ImportedZone
{
    public function __construct(
        public ZoneName $name,
        public ZoneBoundary $boundary,
    ) {}
}
