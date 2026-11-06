<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Coverage\UseCases\ImportZonesUseCase;
use App\Domain\Admin\Repositories\AdminAccountRepository;
use App\Domain\Coverage\Repositories\ZoneCatalogRepository;
use App\Domain\Coverage\Repositories\ZoneFileReader;
use App\Domain\Plans\Repositories\PlanCatalogRepository;
use App\Infrastructure\Adapters\KmlZoneFileReader;
use App\Infrastructure\Persistence\Repositories\EloquentAdminAccountRepository;
use App\Infrastructure\Persistence\Repositories\EloquentPlanCatalogRepository;
use App\Infrastructure\Persistence\Repositories\EloquentZoneCatalogRepository;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

final class AdminServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    public array $bindings = [
        AdminAccountRepository::class => EloquentAdminAccountRepository::class,
        ZoneCatalogRepository::class => EloquentZoneCatalogRepository::class,
        ZoneFileReader::class => KmlZoneFileReader::class,
        PlanCatalogRepository::class => EloquentPlanCatalogRepository::class,
    ];

    public function register(): void
    {
        $this->app->when(KmlZoneFileReader::class)
            ->needs('$maxKilobytes')
            ->giveConfig('admin.kml_max_kb');

        $this->app->when(KmlZoneFileReader::class)
            ->needs('$maxFileVertices')
            ->giveConfig('admin.kml_max_file_vertices');

        $this->app->when(ImportZonesUseCase::class)
            ->needs('$maxPolygonVertices')
            ->giveConfig('admin.kml_max_polygon_vertices');
    }

    public function boot(): void
    {
        $proxies = config('admin.trusted_proxies');

        if ($proxies !== null) {
            TrustProxies::at($proxies);
        }

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }
    }
}
