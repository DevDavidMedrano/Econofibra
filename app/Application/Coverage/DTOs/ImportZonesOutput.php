<?php

declare(strict_types=1);

namespace App\Application\Coverage\DTOs;

final readonly class ImportZonesOutput
{
    /**
     * @param  list<string>  $created
     * @param  list<string>  $replaced
     */
    public function __construct(
        public array $created,
        public array $replaced,
        public int $ignoredGeometries,
    ) {}
}
