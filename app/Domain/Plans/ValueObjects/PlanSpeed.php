<?php

declare(strict_types=1);

namespace App\Domain\Plans\ValueObjects;

use InvalidArgumentException;

final readonly class PlanSpeed
{
    private const MIN_MBPS = 1;

    private const MAX_MBPS = 100000;

    public function __construct(public int $mbps)
    {
        if ($mbps < self::MIN_MBPS || $mbps > self::MAX_MBPS) {
            throw new InvalidArgumentException('La velocidad debe ser un número entero de 1 a 100 000 Mbps.');
        }
    }
}
