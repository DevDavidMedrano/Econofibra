<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Coverage\Repositories\SampleDataRepository;
use App\Infrastructure\Persistence\Eloquent\CoverageZoneModel;
use App\Infrastructure\Persistence\Eloquent\PlanModel;
use App\Infrastructure\Persistence\Eloquent\ZonePlanPriceModel;
use App\Infrastructure\Persistence\Eloquent\ZoneTechnologyModel;

final class EloquentSampleDataRepository implements SampleDataRepository
{
    public function hasSampleData(): bool
    {
        return CoverageZoneModel::query()->where('is_sample', true)->exists()
            || ZoneTechnologyModel::query()->where('is_sample', true)->exists()
            || PlanModel::query()->where('is_sample', true)->exists()
            || ZonePlanPriceModel::query()->where('is_sample', true)->exists();
    }
}
