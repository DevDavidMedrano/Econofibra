<?php

declare(strict_types=1);

namespace App\Domain\Coverage\ValueObjects;

/**
 * The zones, statuses and prices as they are stored at one moment, read by SampleCleanupPolicy.
 */
final readonly class SampleSnapshot
{
    /**
     * @param  list<array{name: string, is_sample: bool, boundary_replaced: bool}>  $zones
     * @param  list<array{zone: string, technology: string, is_sample: bool, zone_is_sample: bool}>  $statuses
     * @param  list<array{zone: string, plan: ?string, is_sample: bool, zone_is_sample: bool, plan_is_sample: bool}>  $prices
     */
    public function __construct(
        public array $zones,
        public array $statuses,
        public array $prices,
    ) {}
}
