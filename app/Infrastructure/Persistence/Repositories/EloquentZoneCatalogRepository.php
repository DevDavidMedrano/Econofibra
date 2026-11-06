<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Coverage\Entities\ImportedZone;
use App\Domain\Coverage\Entities\ZoneCatalogItem;
use App\Domain\Coverage\Entities\ZoneImportResult;
use App\Domain\Coverage\Repositories\ZoneCatalogRepository;
use App\Domain\Coverage\ValueObjects\GeoPoint;
use App\Domain\Coverage\ValueObjects\TechnologyStatus;
use App\Domain\Coverage\ValueObjects\TechnologyType;
use App\Domain\Coverage\ValueObjects\ZoneBoundary;
use App\Domain\Coverage\ValueObjects\ZoneName;
use App\Domain\Shared\AdminChangeRejected;
use App\Infrastructure\Persistence\Eloquent\CoverageZoneModel;
use App\Infrastructure\Persistence\Eloquent\TechnologyModel;
use App\Infrastructure\Persistence\Eloquent\ZoneTechnologyModel;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Zones are matched by name_key and never by name, because MySQL collations ignore accents.
 */
final class EloquentZoneCatalogRepository implements ZoneCatalogRepository
{
    private const MISSING_ZONE = 'La zona ya no existe.';

    public function all(): array
    {
        $statuses = ZoneTechnologyModel::query()
            ->join('technologies', 'technologies.id', '=', 'zone_technologies.technology_id')
            ->get([
                'zone_technologies.zone_id',
                'zone_technologies.status',
                'zone_technologies.is_sample',
                'technologies.code',
            ])
            ->groupBy('zone_id');

        return CoverageZoneModel::query()
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name', 'is_sample'])
            ->map(fn (CoverageZoneModel $zone): ZoneCatalogItem => new ZoneCatalogItem(
                $zone->id,
                $zone->name,
                $this->withEveryTechnology($statuses->get($zone->id, collect())),
                $zone->is_sample,
                $statuses->get($zone->id, collect())->where('is_sample', true)->pluck('code')->values()->all(),
            ))
            ->all();
    }

    public function import(array $zones): ZoneImportResult
    {
        return $this->guarded(fn (): ZoneImportResult => DB::transaction(function () use ($zones): ZoneImportResult {
            $existing = CoverageZoneModel::query()
                ->whereIn('name_key', array_map(static fn (ImportedZone $zone): string => $zone->name->key(), $zones))
                ->lockForUpdate()
                ->get()
                ->keyBy('name_key');
            $created = [];
            $replaced = [];

            foreach ($zones as $zone) {
                $current = $existing->get($zone->name->key());
                $boundary = $this->toStored($zone->boundary);

                if ($current === null) {
                    CoverageZoneModel::query()->create(['name' => $zone->name->value, 'boundary' => $boundary]);
                    $created[] = $zone->name->value;
                } else {
                    $current->update(['boundary' => $boundary, 'boundary_replaced' => true]);
                    $replaced[] = $current->name;
                }
            }

            return new ZoneImportResult($created, $replaced);
        }));
    }

    public function rename(int $zoneId, ZoneName $name): void
    {
        $this->guarded(fn (): bool => $this->find($zoneId)->update(['name' => $name->value]));
    }

    public function delete(int $zoneId): void
    {
        // Statuses and prices go with it through the cascade of their foreign keys.
        $this->guarded(fn (): ?bool => $this->find($zoneId)->delete());
    }

    public function setTechnologyStatus(int $zoneId, TechnologyType $technology, TechnologyStatus $status): void
    {
        $this->guarded(function () use ($zoneId, $technology, $status): void {
            $technologyId = TechnologyModel::query()->where('code', $technology->value)->value('id')
                ?? throw new AdminChangeRejected('La tecnología no existe.');

            // Only the status is written: a new one is real and an edited one keeps its mark.
            ZoneTechnologyModel::query()->updateOrCreate(
                ['zone_id' => $this->find($zoneId)->id, 'technology_id' => $technologyId],
                ['status' => $status->value],
            );
        });
    }

    private function find(int $zoneId): CoverageZoneModel
    {
        return CoverageZoneModel::query()->find($zoneId) ?? throw new AdminChangeRejected(self::MISSING_ZONE);
    }

    /**
     * @template T
     *
     * @param  Closure(): T  $change
     * @return T
     */
    private function guarded(Closure $change): mixed
    {
        try {
            return $change();
        } catch (UniqueConstraintViolationException $repeated) {
            throw new AdminChangeRejected('Ya hay otra zona con ese nombre.', previous: $repeated);
        } catch (QueryException $failure) {
            report($failure);

            throw new AdminChangeRejected('No se pudo guardar el cambio. Inténtalo de nuevo.', previous: $failure);
        }
    }

    /**
     * @param  Collection<int, ZoneTechnologyModel>  $rows
     * @return array<string, TechnologyStatus>
     */
    private function withEveryTechnology(Collection $rows): array
    {
        $statuses = [];

        foreach (TechnologyType::cases() as $technology) {
            $statuses[$technology->value] = TechnologyStatus::Unavailable;
        }

        foreach ($rows as $row) {
            $statuses[$row->code] = TechnologyStatus::from($row->status);
        }

        return $statuses;
    }

    /** @return list<list<array{float, float}>> */
    private function toStored(ZoneBoundary $boundary): array
    {
        return array_map(
            static fn (array $polygon): array => array_map(
                static fn (GeoPoint $vertex): array => [$vertex->latitude, $vertex->longitude],
                $polygon,
            ),
            $boundary->polygons(),
        );
    }
}
