<?php

declare(strict_types=1);

namespace App\Domain\Plans\ValueObjects;

use InvalidArgumentException;

final readonly class PlanName
{
    private const MAX_LENGTH = 100;

    public string $value;

    public function __construct(string $value)
    {
        $name = trim($value);

        if ($name === '' || ! mb_check_encoding($name, 'UTF-8') || mb_strlen($name, 'UTF-8') > self::MAX_LENGTH) {
            throw new InvalidArgumentException('El nombre del plan debe tener de 1 a '.self::MAX_LENGTH.' caracteres.');
        }

        $this->value = $name;
    }
}
