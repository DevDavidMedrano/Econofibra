<?php

declare(strict_types=1);

namespace App\Domain\Coverage\ValueObjects;

final readonly class SampleCounts
{
    public function __construct(
        public int $zones,
        public int $statuses,
        public int $plans,
        public int $prices,
    ) {}

    public function isEmpty(): bool
    {
        return $this->zones + $this->statuses + $this->plans + $this->prices === 0;
    }
}
