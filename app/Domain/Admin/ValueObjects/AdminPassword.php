<?php

declare(strict_types=1);

namespace App\Domain\Admin\ValueObjects;

use InvalidArgumentException;
use SensitiveParameter;

final readonly class AdminPassword
{
    private const MIN_LENGTH = 12;

    public function __construct(#[SensitiveParameter] public string $value)
    {
        if (mb_strlen($value) < self::MIN_LENGTH) {
            throw new InvalidArgumentException('La contraseña debe tener al menos '.self::MIN_LENGTH.' caracteres.');
        }
    }
}
