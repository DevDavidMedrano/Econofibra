<?php

declare(strict_types=1);

namespace App\Domain\Coverage\ValueObjects;

use InvalidArgumentException;

final readonly class ZoneName
{
    private const MAX_LENGTH = 100;

    public string $value;

    public function __construct(string $value)
    {
        $name = trim($value);

        if ($name === '' || ! mb_check_encoding($name, 'UTF-8') || mb_strlen($name, 'UTF-8') > self::MAX_LENGTH) {
            throw new InvalidArgumentException('El nombre de la zona debe tener de 1 a '.self::MAX_LENGTH.' caracteres.');
        }

        $this->value = $name;
    }

    /** Two names with the same key are the same zone: case is ignored, accents are not. */
    public function key(): string
    {
        return hash('sha256', mb_strtolower($this->value, 'UTF-8'));
    }
}
