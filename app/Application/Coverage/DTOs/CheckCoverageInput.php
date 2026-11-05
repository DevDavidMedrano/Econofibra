<?php

declare(strict_types=1);

namespace App\Application\Coverage\DTOs;

/**
 * What the browser sends for a query, already validated. The location travels inside
 * this object so that it never shows as a scalar argument in a logged stack trace (RF-30).
 */
final readonly class CheckCoverageInput
{
    public function __construct(
        public float $latitude,
        public float $longitude,
        public ?float $accuracyMeters,
    ) {}
}
