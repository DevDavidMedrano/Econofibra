<?php

declare(strict_types=1);

namespace App\Domain\Coverage\Entities;

final readonly class ZoneImportResult
{
    /**
     * @param  list<string>  $created
     * @param  list<string>  $replaced
     */
    public function __construct(
        public array $created,
        public array $replaced,
    ) {}
}
