<?php

declare(strict_types=1);

namespace App\Domain\Coverage\ValueObjects;

/**
 * A real record that depends on sample data, with what must be corrected before deleting it.
 */
final readonly class SampleBlocker
{
    public function __construct(
        public string $record,
        public string $correction,
    ) {}
}
