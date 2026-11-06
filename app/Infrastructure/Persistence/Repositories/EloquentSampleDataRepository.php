<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Coverage\Repositories\SampleDataRepository;
use App\Domain\Coverage\Services\SampleCleanupPolicy;
use App\Domain\Coverage\ValueObjects\SampleCounts;
use App\Domain\Coverage\ValueObjects\SampleSnapshot;
use App\Domain\Shared\AdminChangeRejected;
use App\Infrastructure\Persistence\Eloquent\CoverageZoneModel;
use App\Infrastructure\Persistence\Eloquent\PlanModel;
use App\Infrastructure\Persistence\Eloquent\ZonePlanPriceModel;
use App\Infrastructure\Persistence\Eloquent\ZoneTechnologyModel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final class EloquentSampleDataRepository implements SampleDataRepository
{
    public function __construct(private SampleCleanupPolicy $policy) {}

    public function hasSampleData(): bool
    {
        return CoverageZoneModel::query()->where('is_sample', true)->exists()
            || ZoneTechnologyModel::query()->where('is_sample', true)->exists()
            || PlanModel::query()->where('is_sample', true)->exists()
            || ZonePlanPriceModel::query()->where('is_sample', true)->exists();
    }

    public function counts(): SampleCounts
    {
        return new SampleCounts(
            CoverageZoneModel::query()->where('is_sample', true)->count(),
            ZoneTechnologyModel::query()->where('is_sample', true)->count(),
            PlanModel::query()->where('is_sample', true)->count(),
            ZonePlanPriceModel::query()->where('is_sample', true)->count(),
        );
    }

    public function blockers(): array
    {
        return $this->policy->blockers($this->snapshot());
    }

    public function deleteAllUnlessBlocked(): array
    {
        try {
            return DB::transaction(function (): array {
                // A status or price needs its zone and plan, so locking them makes any new one
                // wait for this transaction: nothing real can appear after the check.
                CoverageZoneModel::query()->where('is_sample', true)->lockForUpdate()->get(['id']);
                PlanModel::query()->where('is_sample', true)->lockForUpdate()->get(['id']);

                $blockers = $this->blockers();

                if ($blockers !== []) {
                    return $blockers;
                }

                ZonePlanPriceModel::query()->where('is_sample', true)->delete();
                ZoneTechnologyModel::query()->where('is_sample', true)->delete();
                PlanModel::query()->where('is_sample', true)->delete();
                CoverageZoneModel::query()->where('is_sample', true)->delete();

                return [];
            });
        } catch (QueryException $failure) {
            report($failure);

            throw new AdminChangeRejected('No se pudieron borrar los datos de ejemplo. No se borró nada.', previous: $failure);
        }
    }

    private function snapshot(): SampleSnapshot
    {
        return new SampleSnapshot(
            CoverageZoneModel::query()
                ->orderBy('name')
                ->get(['name', 'is_sample', 'boundary_replaced'])
                ->map(static fn (CoverageZoneModel $zone): array => [
                    'name' => $zone->name,
                    'is_sample' => $zone->is_sample,
                    'boundary_replaced' => $zone->boundary_replaced,
                ])
                ->all(),
            ZoneTechnologyModel::query()
                ->join('coverage_zones', 'coverage_zones.id', '=', 'zone_technologies.zone_id')
                ->join('technologies', 'technologies.id', '=', 'zone_technologies.technology_id')
                ->orderBy('coverage_zones.name')
                ->orderBy('technologies.name')
                ->get([
                    'zone_technologies.is_sample',
                    'coverage_zones.name as zone_name',
                    'coverage_zones.is_sample as zone_is_sample',
                    'technologies.name as technology_name',
                ])
                ->map(static fn (ZoneTechnologyModel $status): array => [
                    'zone' => $status->zone_name,
                    'technology' => $status->technology_name,
                    'is_sample' => $status->is_sample,
                    'zone_is_sample' => (bool) $status->zone_is_sample,
                ])
                ->all(),
            ZonePlanPriceModel::query()
                ->join('coverage_zones', 'coverage_zones.id', '=', 'zone_plan_prices.zone_id')
                ->join('plans', 'plans.id', '=', 'zone_plan_prices.plan_id')
                ->orderBy('coverage_zones.name')
                ->orderBy('plans.name')
                ->get([
                    'zone_plan_prices.is_sample',
                    'coverage_zones.name as zone_name',
                    'coverage_zones.is_sample as zone_is_sample',
                    'plans.name as plan_name',
                    'plans.is_sample as plan_is_sample',
                ])
                ->map(static fn (ZonePlanPriceModel $price): array => [
                    'zone' => $price->zone_name,
                    'plan' => $price->plan_name,
                    'is_sample' => $price->is_sample,
                    'zone_is_sample' => (bool) $price->zone_is_sample,
                    'plan_is_sample' => (bool) $price->plan_is_sample,
                ])
                ->all(),
        );
    }
}
