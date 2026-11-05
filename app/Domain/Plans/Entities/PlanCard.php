<?php

declare(strict_types=1);

namespace App\Domain\Plans\Entities;

/**
 * Informative card of a commercial plan. It says nothing about the plans of a zone.
 * Any value can be missing in the stored data, and PlanCardCatalogPolicy decides
 * whether the card can be shown.
 */
final readonly class PlanCard
{
    public function __construct(
        public int $id,
        public ?string $name,
        public ?int $speedMbps,
        public ?string $phrase,
        public ?string $iconKey,
        public ?int $position,
        public bool $isPopular,
    ) {}
}
