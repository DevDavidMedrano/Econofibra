<?php

declare(strict_types=1);

namespace App\Application\Coverage\DTOs;

use App\Domain\Coverage\Entities\CoverageResult;

final readonly class CheckCoverageOutput
{
    public function __construct(
        public CoverageResult $result,
        public bool $sampleData,
    ) {}
}
