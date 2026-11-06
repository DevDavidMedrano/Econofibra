<?php

declare(strict_types=1);

namespace App\Application\Coverage\UseCases;

use App\Domain\Coverage\Repositories\SampleDataRepository;
use App\Domain\Coverage\ValueObjects\SampleBlocker;
use App\Domain\Shared\AdminChangeRejected;

final class DeleteSampleDataUseCase
{
    public function __construct(private SampleDataRepository $sampleData) {}

    /**
     * @return list<SampleBlocker> Empty when the sample data was deleted; otherwise nothing changed.
     *
     * @throws AdminChangeRejected
     */
    public function execute(): array
    {
        return $this->sampleData->deleteAllUnlessBlocked();
    }
}
