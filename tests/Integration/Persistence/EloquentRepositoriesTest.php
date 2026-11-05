<?php

declare(strict_types=1);

namespace Tests\Integration\Persistence;

use App\Domain\Coverage\Entities\CoverageZone;
use App\Domain\Coverage\Entities\TechnologyOffer;
use App\Domain\Coverage\Repositories\CoverageZoneRepository;
use App\Domain\Coverage\Repositories\SampleDataRepository;
use App\Domain\Coverage\Repositories\ZoneOfferRepository;
use App\Domain\Coverage\Services\CoverageResolver;
use App\Domain\Coverage\ValueObjects\CoverageScenario;
use App\Domain\Coverage\ValueObjects\GeoPoint;
use App\Domain\Coverage\ValueObjects\TechnologyStatus;
use App\Domain\Coverage\ValueObjects\TechnologyType;
use App\Domain\Plans\Entities\InternetPlan;
use App\Domain\Plans\ValueObjects\Money;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use ValueError;

final class EloquentRepositoriesTest extends TestCase
{
    use RefreshDatabase;

    private const SQUARE = '[[[20.0,-100.0],[20.0,-99.9],[20.1,-99.9],[20.1,-100.0]]]';

    public function test_zones_are_read_as_domain_entities(): void
    {
        $id = $this->zone('Centro');

        $zones = $this->zones()->all();

        $this->assertCount(1, $zones);
        $this->assertInstanceOf(CoverageZone::class, $zones[0]);
        $this->assertSame($id, $zones[0]->id);
        $this->assertSame('Centro', $zones[0]->name);
        $this->assertTrue($zones[0]->boundary->contains(new GeoPoint(20.05, -99.95)));
        $this->assertFalse($zones[0]->boundary->contains(new GeoPoint(20.2, -99.95)));
    }

    public function test_an_empty_table_has_no_zones(): void
    {
        $this->assertSame([], $this->zones()->all());
    }

    public function test_a_zone_can_have_several_polygons(): void
    {
        $this->zone('Dos partes', '[[[20,-100],[20,-99.9],[20.1,-99.9]],[[30,-100],[30,-99.9],[30.1,-99.9]]]');

        $boundary = $this->zones()->all()[0]->boundary;

        $this->assertTrue($boundary->contains(new GeoPoint(20.01, -99.95)));
        $this->assertTrue($boundary->contains(new GeoPoint(30.01, -99.95)));
    }

    public function test_a_closed_ring_is_accepted(): void
    {
        $this->zone('Cerrada', '[[[20,-100],[20,-99.9],[20.1,-99.9],[20.1,-100],[20,-100]]]');

        $this->assertTrue($this->zones()->all()[0]->boundary->contains(new GeoPoint(20.05, -99.95)));
    }

    #[DataProvider('malformedBoundaries')]
    public function test_a_malformed_zone_is_a_failure_and_not_an_empty_area(string $boundary): void
    {
        $this->zone('Correcta');
        $this->zone('Mal formada', $boundary);

        $this->expectException(InvalidArgumentException::class);

        $this->zones()->all();
    }

    /** @return array<string, array{string}> */
    public static function malformedBoundaries(): array
    {
        return [
            'not JSON' => ['not json'],
            'JSON null' => ['null'],
            'a number' => ['5'],
            'no polygons' => ['[]'],
            'object instead of a list' => ['{"polygons":[]}'],
            'polygon is not a list' => ['[5]'],
            'polygon with two vertices' => ['[[[20,-100],[20,-99.9]]]'],
            'vertices without a polygon' => ['[[20,-100],[20,-99.9],[20.1,-99.9]]'],
            'vertex with one number' => ['[[[20,-100],[20,-99.9],[20.1]]]'],
            'vertex with three numbers' => ['[[[20,-100],[20,-99.9],[20.1,-99.9,5]]]'],
            'vertex with named keys' => ['[[[20,-100],[20,-99.9],{"lat":20.1,"lng":-99.9}]]'],
            'vertex with text' => ['[[[20,-100],[20,-99.9],["20.1","-99.9"]]]'],
            'vertex with null' => ['[[[20,-100],[20,-99.9],[null,-99.9]]]'],
            'latitude out of range' => ['[[[20,-100],[20,-99.9],[90.1,-99.9]]]'],
            'longitude out of range' => ['[[[20,-100],[20,-99.9],[20.1,-180.1]]]'],
        ];
    }

    public function test_offers_are_read_as_domain_entities_with_the_prices_of_the_zone(): void
    {
        $north = $this->zone('Norte');
        $south = $this->zone('Sur');
        $fiber = $this->technology('fiber', 'Fibra óptica');
        $plan = $this->plan($fiber, 'Fibra 50', 50);
        $this->zoneStatus($north, $fiber, 'available');
        $this->zoneStatus($south, $fiber, 'available');
        $this->price($north, $plan, 40000, 50000);
        $this->price($south, $plan, 45000, 0);

        $offers = $this->offers()->forZone($north);

        $this->assertCount(1, $offers);
        $this->assertInstanceOf(TechnologyOffer::class, $offers[0]);
        $this->assertSame(TechnologyType::Fiber, $offers[0]->type);
        $this->assertSame('Fibra óptica', $offers[0]->name);
        $this->assertSame(TechnologyStatus::Available, $offers[0]->status);
        $this->assertCount(1, $offers[0]->plans);
        $this->assertInstanceOf(InternetPlan::class, $offers[0]->plans[0]);
        $this->assertSame('Fibra 50', $offers[0]->plans[0]->name);
        $this->assertSame(50, $offers[0]->plans[0]->speedMbps);
        $this->assertEquals(new Money(40000), $offers[0]->plans[0]->price);
        $this->assertEquals(new Money(50000), $offers[0]->plans[0]->installationCost);

        $southPlan = $this->offers()->forZone($south)[0]->plans[0];
        $this->assertEquals(new Money(45000), $southPlan->price);
        $this->assertEquals(new Money(0), $southPlan->installationCost);
    }

    #[DataProvider('statuses')]
    public function test_each_stored_status_is_read(string $stored, TechnologyStatus $expected): void
    {
        $zone = $this->zone('Centro');
        $this->zoneStatus($zone, $this->technology('fiber', 'Fibra óptica'), $stored);

        $this->assertSame($expected, $this->offers()->forZone($zone)[0]->status);
    }

    /** @return array<string, array{string, TechnologyStatus}> */
    public static function statuses(): array
    {
        return [
            'available' => ['available', TechnologyStatus::Available],
            'pending review' => ['pending_review', TechnologyStatus::PendingReview],
            'unavailable' => ['unavailable', TechnologyStatus::Unavailable],
        ];
    }

    public function test_an_unknown_status_is_a_failure(): void
    {
        $zone = $this->zone('Centro');
        $this->zoneStatus($zone, $this->technology('fiber', 'Fibra óptica'), 'maybe');

        $this->expectException(ValueError::class);

        $this->offers()->forZone($zone);
    }

    public function test_a_technology_without_status_in_the_zone_is_read_as_unavailable(): void
    {
        $zone = $this->zone('Centro');
        $fiber = $this->technology('fiber', 'Fibra óptica');
        $this->technology('antenna', 'Internet por antena');
        $this->zoneStatus($zone, $fiber, 'available');

        $offers = $this->offers()->forZone($zone);

        $this->assertCount(2, $offers);
        $this->assertSame(TechnologyType::Antenna, $offers[1]->type);
        $this->assertSame('Internet por antena', $offers[1]->name);
        $this->assertSame(TechnologyStatus::Unavailable, $offers[1]->status);
    }

    public function test_the_status_of_another_zone_does_not_apply(): void
    {
        $zone = $this->zone('Centro');
        $other = $this->zone('Otra');
        $this->zoneStatus($other, $this->technology('fiber', 'Fibra óptica'), 'available');

        $this->assertSame(TechnologyStatus::Unavailable, $this->offers()->forZone($zone)[0]->status);
    }

    public function test_a_price_without_status_does_not_confirm_the_technology(): void
    {
        $zone = $this->zone('Centro');
        $fiber = $this->technology('fiber', 'Fibra óptica');
        $this->price($zone, $this->plan($fiber, 'Fibra 50', 50), 40000, 0);

        $result = app(CoverageResolver::class)->resolve($this->zones()->all()[0], $this->offers()->forZone($zone));

        $this->assertSame(CoverageScenario::D, $result->scenario);
    }

    public function test_a_plan_without_a_price_in_the_zone_is_not_assigned_to_it(): void
    {
        $zone = $this->zone('Centro');
        $other = $this->zone('Otra');
        $fiber = $this->technology('fiber', 'Fibra óptica');
        $this->zoneStatus($zone, $fiber, 'available');
        $this->price($other, $this->plan($fiber, 'Solo en otra zona', 50), 40000, 0);

        $this->assertSame([], $this->offers()->forZone($zone)[0]->plans);
    }

    public function test_each_plan_stays_under_its_own_technology_in_a_stable_order(): void
    {
        $zone = $this->zone('Centro');
        $fiber = $this->technology('fiber', 'Fibra óptica');
        $antenna = $this->technology('antenna', 'Internet por antena');
        $first = $this->plan($fiber, 'Fibra 50', 50);
        $second = $this->plan($antenna, 'Antena 20', 20);
        $third = $this->plan($fiber, 'Fibra 100', 100);
        $this->price($zone, $third, 60000, 0);
        $this->price($zone, $second, 30000, 0);
        $this->price($zone, $first, 40000, 0);

        $offers = $this->offers()->forZone($zone);

        $this->assertSame(['Fibra 50', 'Fibra 100'], array_map(fn ($plan) => $plan->name, $offers[0]->plans));
        $this->assertSame(['Antena 20'], array_map(fn ($plan) => $plan->name, $offers[1]->plans));
    }

    public function test_incomplete_values_are_read_as_they_are_for_the_domain_to_filter(): void
    {
        $zone = $this->zone('Centro');
        $fiber = $this->technology('fiber', 'Fibra óptica');
        $this->price($zone, $this->plan($fiber, null, null), null, null);
        $this->price($zone, $this->plan($fiber, 'Negativo', -5), -100, -1);

        [$empty, $negative] = $this->offers()->forZone($zone)[0]->plans;

        $this->assertNull($empty->name);
        $this->assertNull($empty->speedMbps);
        $this->assertNull($empty->price);
        $this->assertNull($empty->installationCost);
        $this->assertSame(-5, $negative->speedMbps);
        $this->assertEquals(new Money(-100), $negative->price);
        $this->assertEquals(new Money(-1), $negative->installationCost);
    }

    public function test_a_change_in_the_database_is_seen_by_the_next_read(): void
    {
        $zone = $this->zone('Centro');
        $fiber = $this->technology('fiber', 'Fibra óptica');
        $plan = $this->plan($fiber, 'Fibra 50', 50);
        $this->zoneStatus($zone, $fiber, 'available');
        $this->price($zone, $plan, 40000, 50000);
        $this->offers()->forZone($zone);

        DB::table('plans')->where('id', $plan)->update(['speed_mbps' => 75]);
        DB::table('zone_plan_prices')->where('plan_id', $plan)->update([
            'price_cents' => 41000,
            'installation_cost_cents' => 0,
        ]);

        $updated = $this->offers()->forZone($zone)[0]->plans[0];
        $this->assertSame(75, $updated->speedMbps);
        $this->assertEquals(new Money(41000), $updated->price);
        $this->assertEquals(new Money(0), $updated->installationCost);
    }

    public function test_there_is_no_sample_data_in_an_empty_database(): void
    {
        $this->assertFalse($this->sampleData()->hasSampleData());
    }

    public function test_real_records_are_not_sample_data(): void
    {
        $this->realCatalog();

        $this->assertFalse($this->sampleData()->hasSampleData());
    }

    #[DataProvider('sampleTables')]
    public function test_a_single_marked_record_among_real_data_is_detected(string $table): void
    {
        $ids = $this->realCatalog();

        DB::table($table)->where('id', $ids[$table])->update(['is_sample' => true]);

        $this->assertTrue($this->sampleData()->hasSampleData());
    }

    /** @return array<string, array{string}> */
    public static function sampleTables(): array
    {
        return [
            'zone' => ['coverage_zones'],
            'technology status' => ['zone_technologies'],
            'plan' => ['plans'],
            'price' => ['zone_plan_prices'],
        ];
    }

    public function test_marking_one_record_leaves_the_others_unmarked(): void
    {
        $ids = $this->realCatalog();
        $second = $this->plan((int) DB::table('technologies')->value('id'), 'Fibra 100', 100);

        DB::table('plans')->where('id', $ids['plans'])->update(['is_sample' => true]);

        $this->assertFalse((bool) DB::table('plans')->where('id', $second)->value('is_sample'));
        $this->assertSame(1, DB::table('plans')->where('is_sample', true)->count());
    }

    public function test_a_zone_has_one_status_per_technology(): void
    {
        $zone = $this->zone('Centro');
        $fiber = $this->technology('fiber', 'Fibra óptica');
        $this->zoneStatus($zone, $fiber, 'available');

        $this->expectException(QueryException::class);

        $this->zoneStatus($zone, $fiber, 'unavailable');
    }

    public function test_a_zone_has_one_price_per_plan(): void
    {
        $zone = $this->zone('Centro');
        $plan = $this->plan($this->technology('fiber', 'Fibra óptica'), 'Fibra 50', 50);
        $this->price($zone, $plan, 40000, 0);

        $this->expectException(QueryException::class);

        $this->price($zone, $plan, 41000, 0);
    }

    private function zones(): CoverageZoneRepository
    {
        return app(CoverageZoneRepository::class);
    }

    private function offers(): ZoneOfferRepository
    {
        return app(ZoneOfferRepository::class);
    }

    private function sampleData(): SampleDataRepository
    {
        return app(SampleDataRepository::class);
    }

    /** @return array<string, int> */
    private function realCatalog(): array
    {
        $zone = $this->zone('Centro');
        $fiber = $this->technology('fiber', 'Fibra óptica');
        $plan = $this->plan($fiber, 'Fibra 50', 50);

        return [
            'coverage_zones' => $zone,
            'zone_technologies' => $this->zoneStatus($zone, $fiber, 'available'),
            'plans' => $plan,
            'zone_plan_prices' => $this->price($zone, $plan, 40000, 0),
        ];
    }

    private function zone(string $name, string $boundary = self::SQUARE): int
    {
        return DB::table('coverage_zones')->insertGetId(['name' => $name, 'boundary' => $boundary]);
    }

    private function technology(string $code, string $name): int
    {
        return DB::table('technologies')->insertGetId(['code' => $code, 'name' => $name]);
    }

    private function zoneStatus(int $zone, int $technology, string $status): int
    {
        return DB::table('zone_technologies')->insertGetId([
            'zone_id' => $zone,
            'technology_id' => $technology,
            'status' => $status,
        ]);
    }

    private function plan(int $technology, ?string $name, ?int $speed): int
    {
        return DB::table('plans')->insertGetId([
            'technology_id' => $technology,
            'name' => $name,
            'speed_mbps' => $speed,
        ]);
    }

    private function price(int $zone, int $plan, ?int $price, ?int $installation): int
    {
        return DB::table('zone_plan_prices')->insertGetId([
            'zone_id' => $zone,
            'plan_id' => $plan,
            'price_cents' => $price,
            'installation_cost_cents' => $installation,
        ]);
    }
}
