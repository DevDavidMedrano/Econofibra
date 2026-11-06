<?php

declare(strict_types=1);

namespace App\Application\Coverage\UseCases;

use App\Application\Coverage\DTOs\SampleSummaryOutput;
use App\Domain\Coverage\Repositories\SampleDataRepository;

final class GetSampleSummaryUseCase
{
    public function __construct(private SampleDataRepository $sampleData) {}

    public function execute(): SampleSummaryOutput
    {
        return new SampleSummaryOutput($this->sampleData->counts(), $this->sampleData->blockers());
    }
}
