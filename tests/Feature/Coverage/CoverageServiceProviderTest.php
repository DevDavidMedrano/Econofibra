<?php

declare(strict_types=1);

namespace Tests\Feature\Coverage;

use App\Application\Coverage\UseCases\CheckCoverageUseCase;
use App\Domain\Coverage\Repositories\CoverageZoneRepository;
use App\Domain\Coverage\Repositories\SampleDataRepository;
use App\Domain\Coverage\Repositories\ZoneOfferRepository;
use App\Infrastructure\Persistence\Repositories\EloquentCoverageZoneRepository;
use App\Infrastructure\Persistence\Repositories\EloquentSampleDataRepository;
use App\Infrastructure\Persistence\Repositories\EloquentZoneOfferRepository;
use Tests\TestCase;

final class CoverageServiceProviderTest extends TestCase
{
    public function test_the_domain_contracts_resolve_to_the_eloquent_repositories(): void
    {
        $this->assertInstanceOf(EloquentCoverageZoneRepository::class, app(CoverageZoneRepository::class));
        $this->assertInstanceOf(EloquentZoneOfferRepository::class, app(ZoneOfferRepository::class));
        $this->assertInstanceOf(EloquentSampleDataRepository::class, app(SampleDataRepository::class));
    }

    public function test_the_use_case_receives_the_configured_threshold(): void
    {
        $binding = $this->app->contextual[CheckCoverageUseCase::class]['$precisionThresholdMeters'];

        $this->assertSame(100, $binding($this->app));

        config(['coverage.precision_threshold_meters' => 80]);

        $this->assertSame(80, $binding($this->app));
    }
}
