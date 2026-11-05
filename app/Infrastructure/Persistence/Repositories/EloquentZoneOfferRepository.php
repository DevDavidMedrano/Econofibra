<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Coverage\Entities\TechnologyOffer;
use App\Domain\Coverage\Repositories\ZoneOfferRepository;
use App\Domain\Coverage\ValueObjects\TechnologyStatus;
use App\Domain\Coverage\ValueObjects\TechnologyType;
use App\Domain\Plans\Entities\InternetPlan;
use App\Domain\Plans\ValueObjects\Money;
use App\Infrastructure\Persistence\Eloquent\TechnologyModel;
use App\Infrastructure\Persistence\Eloquent\ZonePlanPriceModel;
use App\Infrastructure\Persistence\Eloquent\ZoneTechnologyModel;

final class EloquentZoneOfferRepository implements ZoneOfferRepository
{
    public function forZone(int $zoneId): array
    {
        $statuses = ZoneTechnologyModel::query()
            ->where('zone_id', $zoneId)
            ->pluck('status', 'technology_id');

        $prices = ZonePlanPriceModel::query()
            ->with('plan')
            ->where('zone_id', $zoneId)
            ->orderBy('plan_id')
            ->get()
            ->groupBy(static fn (ZonePlanPriceModel $price): int => $price->plan->technology_id);

        return TechnologyModel::query()
            ->orderBy('id')
            ->get()
            ->map(fn (TechnologyModel $technology): TechnologyOffer => new TechnologyOffer(
                TechnologyType::from($technology->code),
                $technology->name,
                TechnologyStatus::from($statuses->get($technology->id, TechnologyStatus::Unavailable->value)),
                $prices->get($technology->id, collect())->map($this->toPlan(...))->all(),
            ))
            ->all();
    }

    private function toPlan(ZonePlanPriceModel $price): InternetPlan
    {
        return new InternetPlan(
            $price->plan->name,
            $price->plan->speed_mbps,
            $this->toMoney($price->price_cents),
            $this->toMoney($price->installation_cost_cents),
        );
    }

    private function toMoney(?int $cents): ?Money
    {
        return $cents === null ? null : new Money($cents);
    }
}
