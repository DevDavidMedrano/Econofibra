<?php

declare(strict_types=1);

namespace App\Domain\Coverage\ValueObjects;

enum CoverageScenario: string
{
    case A = 'A';
    case B = 'B';
    case C = 'C';
    case D = 'D';
    case E = 'E';
}
