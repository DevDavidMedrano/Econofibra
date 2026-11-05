<?php

declare(strict_types=1);

namespace App\Domain\Coverage\Entities;

final readonly class ZoneMatch
{
    private function __construct(
        private bool $ambiguous,
        private ?CoverageZone $zone,
    ) {}

    public static function none(): self
    {
        return new self(false, null);
    }

    public static function single(CoverageZone $zone): self
    {
        return new self(false, $zone);
    }

    public static function ambiguous(): self
    {
        return new self(true, null);
    }

    public function isNone(): bool
    {
        return ! $this->ambiguous && $this->zone === null;
    }

    public function isSingle(): bool
    {
        return $this->zone !== null;
    }

    public function isAmbiguous(): bool
    {
        return $this->ambiguous;
    }

    public function zone(): ?CoverageZone
    {
        return $this->zone;
    }
}
