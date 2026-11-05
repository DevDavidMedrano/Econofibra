<?php

declare(strict_types=1);

namespace App\Domain\Coverage\Services;

use App\Domain\Coverage\ValueObjects\PrecisionMargin;

final class PrecisionPolicy
{
    public function isLow(PrecisionMargin $margin, int $thresholdMeters): bool
    {
        return $margin->meters === null || $margin->meters > $thresholdMeters;
    }
}
