<?php

declare(strict_types=1);

namespace App\Domain\Coverage\ValueObjects;

enum TechnologyType: string
{
    case Fiber = 'fiber';
    case Antenna = 'antenna';
}
