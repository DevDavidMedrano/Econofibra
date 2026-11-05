<?php

declare(strict_types=1);

namespace App\Domain\Plans\ValueObjects;

/**
 * Closed list of icon keys. tryFrom() gives null for any other key, which means the default icon.
 */
enum PlanCardIcon: string
{
    case Casa = 'casa';
    case Control = 'control';
    case Maletin = 'maletin';
    case Cohete = 'cohete';
}
