<?php

declare(strict_types=1);

namespace App\Domain\Coverage\ValueObjects;

/**
 * Why a query ended in Scenario E.
 */
enum CoverageReason: string
{
    case LowPrecision = 'low_precision';
    case AmbiguousLocation = 'ambiguous_location';
    case PendingReview = 'pending_review';
}
