<?php

declare(strict_types=1);

namespace App\Domain\Coverage\Services;

use App\Domain\Coverage\ValueObjects\SampleBlocker;
use App\Domain\Coverage\ValueObjects\SampleSnapshot;

/**
 * Sample data can only be deleted when no real data depends on it (RF-19): deleting a
 * sample zone or plan would otherwise take real prices and statuses with it.
 */
final class SampleCleanupPolicy
{
    private const UNNAMED_PLAN = 'Plan sin nombre';

    /** @return list<SampleBlocker> */
    public function blockers(SampleSnapshot $snapshot): array
    {
        $blockers = [];

        foreach ($snapshot->zones as $zone) {
            if ($zone['is_sample'] && $zone['boundary_replaced']) {
                $blockers[] = new SampleBlocker(
                    "La zona de ejemplo \"{$zone['name']}\" tiene un límite cargado desde un archivo.",
                    'Elimina la zona de ejemplo y vuelve a subir el KML para tenerla como real.',
                );
            }
        }

        foreach ($snapshot->statuses as $status) {
            if (! $status['is_sample'] && $status['zone_is_sample']) {
                $blockers[] = new SampleBlocker(
                    "Estado real de {$status['technology']} en la zona de ejemplo \"{$status['zone']}\".",
                    'Elimina la zona de ejemplo; para conservarla, súbela de nuevo con un KML.',
                );
            }
        }

        foreach ($snapshot->prices as $price) {
            if ($price['is_sample']) {
                continue;
            }

            $plan = $price['plan'] ?? self::UNNAMED_PLAN;

            if ($price['zone_is_sample']) {
                $blockers[] = new SampleBlocker(
                    "Precio real del plan \"{$plan}\" en la zona de ejemplo \"{$price['zone']}\".",
                    'Quita ese precio de la zona o elimina la zona de ejemplo.',
                );
            } elseif ($price['plan_is_sample']) {
                $blockers[] = new SampleBlocker(
                    "Precio real del plan de ejemplo \"{$plan}\" en la zona \"{$price['zone']}\".",
                    'Quita ese precio de la zona o elimina el plan de ejemplo.',
                );
            }
        }

        return $blockers;
    }
}
