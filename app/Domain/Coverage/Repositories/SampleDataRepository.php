<?php

declare(strict_types=1);

namespace App\Domain\Coverage\Repositories;

use App\Domain\Coverage\ValueObjects\SampleBlocker;
use App\Domain\Coverage\ValueObjects\SampleCounts;
use App\Domain\Shared\AdminChangeRejected;

interface SampleDataRepository
{
    /** True when at least one zone, technology status, plan or price is marked as sample. */
    public function hasSampleData(): bool;

    public function counts(): SampleCounts;

    /** @return list<SampleBlocker> The real records that depend on sample data right now. */
    public function blockers(): array;

    /**
     * Deletes every sample record in one operation, unless a real record depends on one.
     * The check and the deletion happen together, so nothing real can slip in between.
     *
     * @return list<SampleBlocker> Empty when the records were deleted; otherwise nothing changed.
     *
     * @throws AdminChangeRejected
     */
    public function deleteAllUnlessBlocked(): array;
}
