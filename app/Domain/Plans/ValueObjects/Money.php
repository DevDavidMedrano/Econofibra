<?php

declare(strict_types=1);

namespace App\Domain\Plans\ValueObjects;

/**
 * An amount in whole cents. It can be zero or negative because stored values are
 * read as they are, and PlanCompletenessPolicy decides whether a plan can be offered.
 */
final readonly class Money
{
    public function __construct(public int $cents) {}

    public function isPositive(): bool
    {
        return $this->cents > 0;
    }

    public function isZero(): bool
    {
        return $this->cents === 0;
    }

    public function isNegative(): bool
    {
        return $this->cents < 0;
    }
}
