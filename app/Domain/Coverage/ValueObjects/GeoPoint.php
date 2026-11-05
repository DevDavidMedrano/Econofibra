<?php

declare(strict_types=1);

namespace App\Domain\Coverage\ValueObjects;

use InvalidArgumentException;

final readonly class GeoPoint
{
    public function __construct(
        public float $latitude,
        public float $longitude,
    ) {
        // Written as "not inside" so that NAN is rejected too.
        if (! ($latitude >= -90.0 && $latitude <= 90.0)) {
            throw new InvalidArgumentException('Latitude must be between -90 and 90.');
        }

        if (! ($longitude >= -180.0 && $longitude <= 180.0)) {
            throw new InvalidArgumentException('Longitude must be between -180 and 180.');
        }
    }
}
