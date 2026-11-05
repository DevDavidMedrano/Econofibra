<?php

declare(strict_types=1);

namespace Tests\Integration\Persistence;

use App\Application\Coverage\DTOs\CheckCoverageInput;
use App\Application\Coverage\UseCases\CheckCoverageUseCase;
use App\Domain\Coverage\Entities\CoverageResult;
use App\Domain\Coverage\Entities\CoverageZone;
use App\Domain\Coverage\Entities\TechnologyOffer;
use App\Domain\Coverage\Repositories\CoverageZoneRepository;
use App\Domain\Coverage\Repositories\SampleDataRepository;
use App\Domain\Coverage\ValueObjects\CoverageReason;
use App\Domain\Coverage\ValueObjects\CoverageScenario;
use App\Domain\Coverage\ValueObjects\GeoPoint;
use App\Domain\Coverage\ValueObjects\TechnologyType;
use App\Domain\Plans\Entities\InternetPlan;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class SampleDataSeederTest extends TestCase
{
    use RefreshDatabase;

    private const SAMPLE_TABLES = ['coverage_zones', 'zone_technologies', 'plans', 'zone_plan_prices'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    #[DataProvider('expectedResults')]
    public function test_the_sample_data_meets_the_table_of_the_plan(
        float $latitude,
        float $longitude,
        ?float $margin,
        CoverageScenario $scenario,
        ?CoverageReason $reason,
        ?string $zone,
    ): void {
        $result = $this->resultFor($latitude, $longitude, $margin);

        $this->assertSame($scenario, $result->scenario);
        $this->assertSame($reason, $result->reason);
        $this->assertSame($zone, $result->zone?->name);
    }

    /** @return array<string, array{float, float, ?float, CoverageScenario, ?CoverageReason, ?string}> */
    public static function expectedResults(): array
    {
        $ambiguous = CoverageReason::AmbiguousLocation;
        $lowPrecision = CoverageReason::LowPrecision;

        return [
            'A in Ejemplo Fibra' => [20.000, -100.000, 30.0, CoverageScenario::A, null, 'Ejemplo Fibra'],
            'B in Ejemplo Antena' => [20.000, -99.950, 30.0, CoverageScenario::B, null, 'Ejemplo Antena'],
            'C in Ejemplo Ambas' => [20.000, -99.900, 30.0, CoverageScenario::C, null, 'Ejemplo Ambas'],
            'D in Ejemplo Sin Servicio' => [20.050, -100.000, 30.0, CoverageScenario::D, null, 'Ejemplo Sin Servicio'],
            'E in Ejemplo Revisión' => [20.050, -99.950, 30.0, CoverageScenario::E, CoverageReason::PendingReview, 'Ejemplo Revisión'],
            'E between Ejemplo Norte and Sur' => [20.051, -99.900, 30.0, CoverageScenario::E, $ambiguous, null],
            'D without zone' => [21.000, -101.000, 30.0, CoverageScenario::D, null, null],
            'D at 52 m with margin 40' => [20.000, -99.9945, 40.0, CoverageScenario::D, null, null],
            'E at 52 m with margin 60' => [20.000, -99.9945, 60.0, CoverageScenario::E, $ambiguous, null],
            'low precision in Ejemplo Fibra' => [20.000, -100.000, 150.0, CoverageScenario::E, $lowPrecision, null],
            'low precision in Ejemplo Antena' => [20.000, -99.950, 150.0, CoverageScenario::E, $lowPrecision, null],
            'low precision in Ejemplo Ambas' => [20.000, -99.900, 150.0, CoverageScenario::E, $lowPrecision, null],
            'low precision in Ejemplo Sin Servicio' => [20.050, -100.000, 150.0, CoverageScenario::E, $lowPrecision, null],
            'low precision in Ejemplo Revisión' => [20.050, -99.950, 150.0, CoverageScenario::E, $lowPrecision, null],
            'low precision in Ejemplo Norte' => [20.050, -99.900, 150.0, CoverageScenario::E, $lowPrecision, null],
            'low precision in Ejemplo Sur' => [20.053, -99.900, 150.0, CoverageScenario::E, $lowPrecision, null],
            'no margin' => [20.000, -100.000, null, CoverageScenario::E, $lowPrecision, null],
        ];
    }

    public function test_scenario_a_offers_two_fiber_plans(): void
    {
        $result = $this->resultFor(20.000, -100.000, 30.0);

        $this->assertSame([TechnologyType::Fiber], $this->types($result));
        $this->assertCount(2, $result->offers[0]->plans);
        $this->assertSame(TechnologyType::Fiber, $result->recommended);
    }

    public function test_scenario_b_offers_two_antenna_plans(): void
    {
        $result = $this->resultFor(20.000, -99.950, 30.0);

        $this->assertSame([TechnologyType::Antenna], $this->types($result));
        $this->assertCount(2, $result->offers[0]->plans);
        $this->assertSame(TechnologyType::Antenna, $result->recommended);
    }

    public function test_scenario_c_offers_two_plans_of_each_technology_and_one_free_installation(): void
    {
        $result = $this->resultFor(20.000, -99.900, 30.0);

        $this->assertSame([TechnologyType::Fiber, TechnologyType::Antenna], $this->types($result));
        $this->assertCount(2, $result->offers[0]->plans);
        $this->assertCount(2, $result->offers[1]->plans);
        $this->assertSame(TechnologyType::Fiber, $result->recommended);

        $plans = array_merge($result->offers[0]->plans, $result->offers[1]->plans);
        $free = array_filter($plans, static fn (InternetPlan $plan): bool => $plan->installationCost->isZero());
        $this->assertCount(1, $free);
    }

    public function test_the_same_plan_has_a_different_price_in_each_zone(): void
    {
        $inFiberZone = $this->resultFor(20.000, -100.000, 30.0)->offers[0]->plans[0];
        $inBothZone = $this->resultFor(20.000, -99.900, 30.0)->offers[0]->plans[0];

        $this->assertSame($inFiberZone->name, $inBothZone->name);
        $this->assertNotEquals($inFiberZone->price, $inBothZone->price);
    }

    public function test_the_overlapping_zones_can_each_be_reached_outside_the_overlap(): void
    {
        $this->assertSame('Ejemplo Norte', $this->resultFor(20.046, -99.900, 30.0)->zone?->name);
        $this->assertSame('Ejemplo Sur', $this->resultFor(20.057, -99.900, 30.0)->zone?->name);
    }

    public function test_the_zones_are_squares_of_a_hundredth_of_a_degree(): void
    {
        $zones = app(CoverageZoneRepository::class)->all();
        $this->assertCount(7, $zones);

        $fiber = array_values(array_filter(
            $zones,
            static fn (CoverageZone $zone): bool => $zone->name === 'Ejemplo Fibra',
        ))[0];

        $this->assertTrue($fiber->boundary->contains(new GeoPoint(20.0049, -100.0049)));
        $this->assertTrue($fiber->boundary->contains(new GeoPoint(19.9951, -99.9951)));
        $this->assertFalse($fiber->boundary->contains(new GeoPoint(20.0051, -100.0)));
        $this->assertFalse($fiber->boundary->contains(new GeoPoint(20.0, -99.9949)));
    }

    public function test_every_sample_record_is_marked(): void
    {
        foreach (self::SAMPLE_TABLES as $table) {
            $this->assertGreaterThan(0, DB::table($table)->count(), $table);
            $this->assertSame(0, DB::table($table)->where('is_sample', false)->count(), $table);
        }

        $this->assertTrue(app(SampleDataRepository::class)->hasSampleData());
    }

    public function test_the_technology_catalog_has_fiber_and_antenna(): void
    {
        $this->assertSame(
            ['antenna' => 'Internet por antena', 'fiber' => 'Fibra óptica'],
            DB::table('technologies')->orderBy('code')->pluck('name', 'code')->all(),
        );
    }

    public function test_seeding_twice_does_not_duplicate_records(): void
    {
        $tables = [...self::SAMPLE_TABLES, 'technologies'];
        $before = array_map(static fn (string $table): int => DB::table($table)->count(), $tables);

        $this->seed(DatabaseSeeder::class);

        $this->assertSame($before, array_map(static fn (string $table): int => DB::table($table)->count(), $tables));
        $this->assertSame(CoverageScenario::A, $this->resultFor(20.000, -100.000, 30.0)->scenario);
    }

    private function resultFor(float $latitude, float $longitude, ?float $meters): CoverageResult
    {
        return app(CheckCoverageUseCase::class)
            ->execute(new CheckCoverageInput($latitude, $longitude, $meters))
            ->result;
    }

    /** @return list<TechnologyType> */
    private function types(CoverageResult $result): array
    {
        return array_map(static fn (TechnologyOffer $offer): TechnologyType => $offer->type, $result->offers);
    }
}
