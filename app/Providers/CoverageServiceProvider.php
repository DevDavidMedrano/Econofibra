<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Coverage\UseCases\CheckCoverageUseCase;
use App\Domain\Coverage\Repositories\CoverageZoneRepository;
use App\Domain\Coverage\Repositories\SampleDataRepository;
use App\Domain\Coverage\Repositories\ZoneOfferRepository;
use App\Infrastructure\Persistence\Repositories\EloquentCoverageZoneRepository;
use App\Infrastructure\Persistence\Repositories\EloquentSampleDataRepository;
use App\Infrastructure\Persistence\Repositories\EloquentZoneOfferRepository;
use Illuminate\Support\ServiceProvider;

final class CoverageServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    public array $bindings = [
        CoverageZoneRepository::class => EloquentCoverageZoneRepository::class,
        ZoneOfferRepository::class => EloquentZoneOfferRepository::class,
        SampleDataRepository::class => EloquentSampleDataRepository::class,
    ];

    public function register(): void
    {
        $this->app->when(CheckCoverageUseCase::class)
            ->needs('$precisionThresholdMeters')
            ->giveConfig('coverage.precision_threshold_meters');
    }
}
