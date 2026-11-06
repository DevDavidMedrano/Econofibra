<?php

declare(strict_types=1);

namespace App\Domain\Plans\Entities;

use App\Domain\Plans\ValueObjects\Money;

/**
 * A plan assigned to a zone. The amounts can be missing in the stored data.
 */
final readonly class ZonePriceItem
{
    public function __construct(
        public PlanCatalogItem $plan,
        public ?Money $price,
        public ?Money $installationCost,
        public bool $isSample,
    ) {}
}
