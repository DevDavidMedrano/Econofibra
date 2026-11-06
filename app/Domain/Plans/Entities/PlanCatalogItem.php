<?php

declare(strict_types=1);

namespace App\Domain\Plans\Entities;

use App\Domain\Coverage\ValueObjects\TechnologyType;

/**
 * A plan as the panel lists it. The name and speed can be missing in the stored data.
 */
final readonly class PlanCatalogItem
{
    public function __construct(
        public int $id,
        public TechnologyType $technology,
        public ?string $name,
        public ?int $speedMbps,
        public bool $isSample,
        public int $zonesWithPrice,
    ) {}
}
