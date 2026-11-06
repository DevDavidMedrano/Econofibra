<?php

declare(strict_types=1);

namespace App\Domain\Admin\ValueObjects;

use InvalidArgumentException;

/**
 * The account email, always normalized. normalize() alone does not validate, so the
 * login can compare any text without answering differently for a bad format.
 */
final readonly class AdminEmail
{
    private const MAX_LENGTH = 255;

    public string $value;

    public function __construct(string $email)
    {
        $normalized = self::normalize($email);

        if (mb_strlen($normalized) > self::MAX_LENGTH || filter_var($normalized, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('El correo no es válido.');
        }

        $this->value = $normalized;
    }

    public static function normalize(string $email): string
    {
        return mb_strtolower(trim($email));
    }
}
