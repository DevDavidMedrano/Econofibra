<?php

declare(strict_types=1);

namespace App\Domain\Coverage\ValueObjects;

enum TechnologyStatus: string
{
    case Available = 'available';
    case PendingReview = 'pending_review';
    case Unavailable = 'unavailable';
}
