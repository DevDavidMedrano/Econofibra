<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Coverage\ValueObjects\TechnologyType;
use App\Domain\Plans\Entities\PlanCatalogItem;
use App\Domain\Plans\Entities\ZonePriceItem;
use App\Domain\Plans\Entities\ZonePriceSheet;
use App\Domain\Plans\Repositories\PlanCatalogRepository;
use App\Domain\Plans\ValueObjects\Money;
use App\Domain\Plans\ValueObjects\PlanName;
use App\Domain\Plans\ValueObjects\PlanSpeed;
use App\Domain\Shared\AdminChangeRejected;
use App\Infrastructure\Persistence\Eloquent\CoverageZoneModel;
use App\Infrastructure\Persistence\Eloquent\PlanModel;
use App\Infrastructure\Persistence\Eloquent\TechnologyModel;
use App\Infrastructure\Persistence\Eloquent\ZonePlanPriceModel;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final class EloquentPlanCatalogRepository implements PlanCatalogRepository
{
    private const MISSING_PLAN = 'El plan ya no existe.';

    private const MISSING_ZONE = 'La zona ya no existe.';

    public function all(): array
    {
        return array_values(array_map($this->toItem(...), PlanModel::query()
            ->join('technologies', 'technologies.id', '=', 'plans.technology_id')
            ->select('plans.*', 'technologies.code as technology_code')
            ->withCount('prices')
            ->orderBy('technologies.code', 'desc')
            ->orderBy('plans.speed_mbps')
            ->orderBy('plans.id')
            ->get()
            ->all()));
    }

    public function create(TechnologyType $technology, PlanName $name, PlanSpeed $speed): void
    {
        $this->guarded(fn (): PlanModel => PlanModel::query()->create([
            'technology_id' => $this->technologyId($technology),
            'name' => $name->value,
            'speed_mbps' => $speed->mbps,
        ]));
    }

    public function update(int $planId, TechnologyType $technology, PlanName $name, PlanSpeed $speed): void
    {
        $this->guarded(fn (): bool => DB::transaction(function () use ($planId, $technology, $name, $speed): bool {
            // The lock makes a price that is being added wait, so the check below stays true.
            $plan = PlanModel::query()->lockForUpdate()->find($planId)
                ?? throw new AdminChangeRejected(self::MISSING_PLAN);
            $technologyId = $this->technologyId($technology);

            if ($plan->technology_id !== $technologyId
                && ZonePlanPriceModel::query()->where('plan_id', $planId)->exists()) {
                throw new AdminChangeRejected('No se puede cambiar la tecnología de un plan con precios. Quítalo antes de sus zonas.');
            }

            return $plan->update([
                'technology_id' => $technologyId,
                'name' => $name->value,
                'speed_mbps' => $speed->mbps,
            ]);
        }));
    }

    public function delete(int $planId): void
    {
        // Its prices go with it through the cascade of their foreign key.
        $this->guarded(fn (): ?bool => (PlanModel::query()->find($planId)
            ?? throw new AdminChangeRejected(self::MISSING_PLAN))->delete());
    }

    public function zonePrices(int $zoneId): ?ZonePriceSheet
    {
        $zone = CoverageZoneModel::query()->find($zoneId, ['id', 'name', 'is_sample']);

        if ($zone === null) {
            return null;
        }

        $stored = ZonePlanPriceModel::query()->where('zone_id', $zoneId)->get()->keyBy('plan_id');
        $prices = [];
        $unassigned = [];

        foreach ($this->all() as $plan) {
            $price = $stored->get($plan->id);

            if ($price === null) {
                $unassigned[] = $plan;

                continue;
            }

            $prices[] = new ZonePriceItem(
                $plan,
                $this->toMoney($price->price_cents),
                $this->toMoney($price->installation_cost_cents),
                $price->is_sample,
            );
        }

        return new ZonePriceSheet($zone->id, $zone->name, $zone->is_sample, $prices, $unassigned);
    }

    public function setZonePrice(int $zoneId, int $planId, Money $price, Money $installationCost): void
    {
        $this->guarded(function () use ($zoneId, $planId, $price, $installationCost): void {
            if (! CoverageZoneModel::query()->whereKey($zoneId)->exists()) {
                throw new AdminChangeRejected(self::MISSING_ZONE);
            }

            if (! PlanModel::query()->whereKey($planId)->exists()) {
                throw new AdminChangeRejected(self::MISSING_PLAN);
            }

            // Only the amounts are written: a new price is real and an edited one keeps its mark.
            ZonePlanPriceModel::query()->updateOrCreate(
                ['zone_id' => $zoneId, 'plan_id' => $planId],
                ['price_cents' => $price->cents, 'installation_cost_cents' => $installationCost->cents],
            );
        });
    }

    public function removeZonePrice(int $zoneId, int $planId): void
    {
        $removed = $this->guarded(fn (): int => ZonePlanPriceModel::query()
            ->where('zone_id', $zoneId)
            ->where('plan_id', $planId)
            ->delete());

        if ($removed === 0) {
            throw new AdminChangeRejected('Ese plan ya no está en la zona.');
        }
    }

    private function technologyId(TechnologyType $technology): int
    {
        $id = TechnologyModel::query()->where('code', $technology->value)->value('id');

        return $id === null ? throw new AdminChangeRejected('La tecnología no existe.') : (int) $id;
    }

    private function toItem(PlanModel $plan): PlanCatalogItem
    {
        return new PlanCatalogItem(
            $plan->id,
            TechnologyType::from($plan->technology_code),
            $plan->name,
            $plan->speed_mbps,
            $plan->is_sample,
            (int) $plan->prices_count,
        );
    }

    private function toMoney(?int $cents): ?Money
    {
        return $cents === null ? null : new Money($cents);
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
        } catch (QueryException $failure) {
            report($failure);

            throw new AdminChangeRejected('No se pudo guardar el cambio. Inténtalo de nuevo.', previous: $failure);
        }
    }
}
