<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Coverage\ValueObjects\TechnologyStatus;
use App\Domain\Coverage\ValueObjects\TechnologyType;
use App\Infrastructure\Persistence\Eloquent\CoverageZoneModel;
use App\Infrastructure\Persistence\Eloquent\PlanModel;
use App\Infrastructure\Persistence\Eloquent\TechnologyModel;
use App\Infrastructure\Persistence\Eloquent\ZonePlanPriceModel;
use App\Infrastructure\Persistence\Eloquent\ZoneTechnologyModel;
use Illuminate\Database\Seeder;

/**
 * Fictitious zones, plans and prices that produce Scenarios A to E (RF-46). Every record
 * is marked as sample, and they must be removed before publishing (RF-26). Running it
 * again updates them, because a duplicated zone would make its points ambiguous.
 */
final class CoverageSampleSeeder extends Seeder
{
    private const HALF_SIDE_DEGREES = 0.005;

    private const FIBER_50 = 'Ejemplo Fibra 50';

    private const FIBER_100 = 'Ejemplo Fibra 100';

    private const ANTENNA_20 = 'Ejemplo Antena 20';

    private const ANTENNA_40 = 'Ejemplo Antena 40';

    private const PLANS = [
        self::FIBER_50 => [TechnologyType::Fiber, 50],
        self::FIBER_100 => [TechnologyType::Fiber, 100],
        self::ANTENNA_20 => [TechnologyType::Antenna, 20],
        self::ANTENNA_40 => [TechnologyType::Antenna, 40],
    ];

    public function run(): void
    {
        $technologies = TechnologyModel::query()->pluck('id', 'code');
        $plans = $this->seedPlans($technologies->all());

        foreach ($this->zones() as $name => $data) {
            $zone = CoverageZoneModel::query()->updateOrCreate(
                ['name' => $name],
                ['boundary' => $this->square(...$data['center']), 'is_sample' => true],
            );

            foreach ($data['statuses'] as [$type, $status]) {
                ZoneTechnologyModel::query()->updateOrCreate(
                    ['zone_id' => $zone->id, 'technology_id' => $technologies[$type->value]],
                    ['status' => $status->value, 'is_sample' => true],
                );
            }

            foreach ($data['prices'] as $plan => [$price, $installation]) {
                ZonePlanPriceModel::query()->updateOrCreate(
                    ['zone_id' => $zone->id, 'plan_id' => $plans[$plan]],
                    ['price_cents' => $price, 'installation_cost_cents' => $installation, 'is_sample' => true],
                );
            }
        }
    }

    /**
     * @param  array<string, int>  $technologies
     * @return array<string, int>
     */
    private function seedPlans(array $technologies): array
    {
        $ids = [];

        foreach (self::PLANS as $name => [$type, $speed]) {
            $ids[$name] = PlanModel::query()->updateOrCreate(
                ['name' => $name, 'technology_id' => $technologies[$type->value]],
                ['speed_mbps' => $speed, 'is_sample' => true],
            )->id;
        }

        return $ids;
    }

    /**
     * Prices are in cents, as [price, installation cost].
     *
     * @return array<string, array{
     *     center: array{float, float},
     *     statuses: list<array{TechnologyType, TechnologyStatus}>,
     *     prices: array<string, array{int, int}>,
     * }>
     */
    private function zones(): array
    {
        $fiberAvailable = [TechnologyType::Fiber, TechnologyStatus::Available];
        $antennaAvailable = [TechnologyType::Antenna, TechnologyStatus::Available];

        return [
            'Ejemplo Fibra' => [
                'center' => [20.000, -100.000],
                'statuses' => [$fiberAvailable],
                'prices' => [self::FIBER_50 => [39900, 50000], self::FIBER_100 => [54900, 50000]],
            ],
            'Ejemplo Antena' => [
                'center' => [20.000, -99.950],
                'statuses' => [$antennaAvailable],
                'prices' => [self::ANTENNA_20 => [34900, 120000], self::ANTENNA_40 => [44900, 120000]],
            ],
            'Ejemplo Ambas' => [
                'center' => [20.000, -99.900],
                'statuses' => [$fiberAvailable, $antennaAvailable],
                'prices' => [
                    self::FIBER_50 => [42900, 0],
                    self::FIBER_100 => [57900, 50000],
                    self::ANTENNA_20 => [32900, 100000],
                    self::ANTENNA_40 => [42900, 100000],
                ],
            ],
            'Ejemplo Sin Servicio' => [
                'center' => [20.050, -100.000],
                'statuses' => [
                    [TechnologyType::Fiber, TechnologyStatus::Unavailable],
                    [TechnologyType::Antenna, TechnologyStatus::Unavailable],
                ],
                'prices' => [],
            ],
            'Ejemplo Revisión' => [
                'center' => [20.050, -99.950],
                'statuses' => [[TechnologyType::Fiber, TechnologyStatus::PendingReview]],
                'prices' => [],
            ],
            'Ejemplo Norte' => [
                'center' => [20.050, -99.900],
                'statuses' => [$fiberAvailable],
                'prices' => [self::FIBER_50 => [40900, 50000]],
            ],
            'Ejemplo Sur' => [
                'center' => [20.053, -99.900],
                'statuses' => [$fiberAvailable],
                'prices' => [self::FIBER_50 => [41900, 50000]],
            ],
        ];
    }

    /** @return list<list<array{float, float}>> */
    private function square(float $latitude, float $longitude): array
    {
        $south = round($latitude - self::HALF_SIDE_DEGREES, 6);
        $north = round($latitude + self::HALF_SIDE_DEGREES, 6);
        $west = round($longitude - self::HALF_SIDE_DEGREES, 6);
        $east = round($longitude + self::HALF_SIDE_DEGREES, 6);

        return [[[$south, $west], [$south, $east], [$north, $east], [$north, $west]]];
    }
}
