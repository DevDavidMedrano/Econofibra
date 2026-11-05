<?php

declare(strict_types=1);

namespace App\Domain\Coverage\Repositories;

interface SampleDataRepository
{
    /** True when at least one zone, technology status, plan or price is marked as sample. */
    public function hasSampleData(): bool;
}
