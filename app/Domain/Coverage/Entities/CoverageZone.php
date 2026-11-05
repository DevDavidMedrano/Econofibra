<?php

declare(strict_types=1);

namespace App\Domain\Coverage\Entities;

use App\Domain\Coverage\ValueObjects\ZoneBoundary;

final readonly class CoverageZone
{
    public function __construct(
        public int $id,
        public string $name,
        public ZoneBoundary $boundary,
    ) {}
}
