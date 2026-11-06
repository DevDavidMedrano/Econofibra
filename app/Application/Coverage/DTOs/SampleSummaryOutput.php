<?php

declare(strict_types=1);

namespace App\Application\Coverage\DTOs;

use App\Domain\Coverage\ValueObjects\SampleBlocker;
use App\Domain\Coverage\ValueObjects\SampleCounts;

final readonly class SampleSummaryOutput
{
    /** @param list<SampleBlocker> $blockers */
    public function __construct(
        public SampleCounts $counts,
        public array $blockers,
    ) {}
}
