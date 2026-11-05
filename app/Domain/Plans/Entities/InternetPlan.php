<?php

declare(strict_types=1);

namespace App\Domain\Plans\Entities;

use App\Domain\Plans\ValueObjects\Money;

/**
 * A plan with the price and installation cost of one zone. Any value can be missing
 * in the stored data, and PlanCompletenessPolicy decides whether it can be offered.
 */
final readonly class InternetPlan
{
    public function __construct(
        public ?string $name,
        public ?int $speedMbps,
        public ?Money $price,
        public ?Money $installationCost,
    ) {}
}
