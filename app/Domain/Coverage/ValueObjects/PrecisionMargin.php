<?php

declare(strict_types=1);

namespace App\Domain\Coverage\ValueObjects;

use InvalidArgumentException;

/**
 * Radius in meters around a point, as reported by the device.
 * Null means the device did not report it.
 */
final readonly class PrecisionMargin
{
    public function __construct(public ?float $meters)
    {
        if ($meters !== null && ! (is_finite($meters) && $meters > 0.0)) {
            throw new InvalidArgumentException('A reported precision margin must be greater than zero.');
        }
    }
}
