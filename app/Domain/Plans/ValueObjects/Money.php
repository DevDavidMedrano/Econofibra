<?php

declare(strict_types=1);

namespace App\Domain\Plans\ValueObjects;

use InvalidArgumentException;

/**
 * An amount in whole cents. It can be zero or negative because stored values are
 * read as they are, and PlanCompletenessPolicy decides whether a plan can be offered.
 */
final readonly class Money
{
    private const CENTS_PER_UNIT = 100;

    public function __construct(public int $cents) {}

    /**
     * Reads an amount typed by a person without going through float, which would lose cents.
     *
     * @throws InvalidArgumentException
     */
    public static function fromDecimalString(string $amount): self
    {
        if (preg_match('/\A(\d{1,6})(?:\.(\d{1,2}))?\z/', $amount, $parts) !== 1) {
            throw new InvalidArgumentException('El importe no es válido.');
        }

        return new self((int) $parts[1] * self::CENTS_PER_UNIT + (int) str_pad($parts[2] ?? '', 2, '0'));
    }

    public function toDecimalString(): string
    {
        $cents = abs($this->cents);

        return sprintf(
            '%s%d.%02d',
            $this->isNegative() ? '-' : '',
            intdiv($cents, self::CENTS_PER_UNIT),
            $cents % self::CENTS_PER_UNIT,
        );
    }

    public function isPositive(): bool
    {
        return $this->cents > 0;
    }

    public function isZero(): bool
    {
        return $this->cents === 0;
    }

    public function isNegative(): bool
    {
        return $this->cents < 0;
    }
}
