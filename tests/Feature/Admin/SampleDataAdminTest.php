<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Infrastructure\Persistence\Eloquent\AdminUserModel;
use App\Infrastructure\Persistence\Eloquent\CoverageZoneModel;
use App\Infrastructure\Persistence\Eloquent\PlanCardModel;
use App\Infrastructure\Persistence\Eloquent\PlanModel;
use App\Infrastructure\Persistence\Eloquent\TechnologyModel;
use App\Infrastructure\Persistence\Eloquent\ZonePlanPriceModel;
use App\Infrastructure\Persistence\Eloquent\ZoneTechnologyModel;
use Database\Seeders\CoverageSampleSeeder;
use Database\Seeders\PlanCardSeeder;
use Database\Seeders\TechnologySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

final class SampleDataAdminTest extends TestCase
{
    use RefreshDatabase;

    private const PANEL_URL = '/panel';

    private const DELETE_URL = '/panel/datos-de-ejemplo';

    private const MARK = 'De ejemplo';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(TechnologySeeder::class);
        $this->seed(PlanCardSeeder::class);
        $this->actingAs(AdminUserModel::query()->create([
            'name' => 'Administrador',
            'email' => 'admin@example.com',
            'password' => 'clave-de-prueba-larga',
        ]));
    }

    public function test_deleting_needs_a_session(): void
    {
        $this->seed(CoverageSampleSeeder::class);
        $this->app['auth']->guard()->logout();
        $this->app['auth']->forgetGuards();

        $this->delete(self::DELETE_URL, ['confirmation' => '1'])->assertRedirect('/panel/entrar');

        $this->assertSame(7, CoverageZoneModel::query()->count());
    }

    public function test_the_panel_shows_how_many_records_of_each_type_remain(): void
    {
        $this->seed(CoverageSampleSeeder::class);
        $this->realZone();

        $response = $this->get(self::PANEL_URL)->assertOk();

        $counts = $response->viewData('summary')->counts;
        $this->assertSame(7, $counts->zones);
        $this->assertSame(ZoneTechnologyModel::query()->count(), $counts->statuses);
        $this->assertSame(4, $counts->plans);
        $this->assertSame(ZonePlanPriceModel::query()->count(), $counts->prices);
        $response->assertSeeInOrder(['Zonas', '7', 'Planes', '4']);
    }

    public function test_the_lists_mark_the_sample_records(): void
    {
        $this->seed(CoverageSampleSeeder::class);

        $this->get('/panel/zonas')->assertSee(self::MARK);
        $this->get('/panel/planes')->assertSee(self::MARK);
        $this->get("/panel/zonas/{$this->sampleZone()->id}/precios")->assertSee(self::MARK);
    }

    public function test_the_lists_do_not_mark_the_real_records(): void
    {
        $zone = $this->realZone();
        $this->realPrice($zone, $this->realPlan());

        $this->get('/panel/zonas')->assertDontSee(self::MARK);
        $this->get('/panel/planes')->assertDontSee(self::MARK);
        $this->get("/panel/zonas/{$zone->id}/precios")->assertDontSee(self::MARK);
    }

    public function test_a_sample_status_is_marked_in_the_zone_list(): void
    {
        ZoneTechnologyModel::query()->create([
            'zone_id' => $this->realZone()->id,
            'technology_id' => $this->technologyId('fiber'),
            'status' => 'available',
            'is_sample' => true,
        ]);

        $this->get('/panel/zonas')->assertSee(self::MARK);
    }

    public function test_the_action_is_inactive_when_no_sample_data_remains(): void
    {
        $this->realZone();

        $this->get(self::PANEL_URL)
            ->assertOk()
            ->assertSee('No quedan datos de ejemplo.')
            ->assertDontSee('name="confirmation"', false);
    }

    public function test_the_action_is_offered_while_sample_data_remains(): void
    {
        $this->seed(CoverageSampleSeeder::class);

        $this->get(self::PANEL_URL)->assertSee('name="confirmation"', false);
    }

    public function test_without_the_confirmation_field_nothing_is_deleted(): void
    {
        $this->seed(CoverageSampleSeeder::class);

        $this->from(self::PANEL_URL)->delete(self::DELETE_URL)->assertSessionHasErrors('confirmation');

        $this->assertSame(7, CoverageZoneModel::query()->count());
        $this->assertSame(4, PlanModel::query()->count());
    }

    public function test_all_the_sample_data_goes_and_the_real_data_stays(): void
    {
        $this->seed(CoverageSampleSeeder::class);
        $zone = $this->realZone();
        $plan = $this->realPlan();
        $this->realPrice($zone, $plan);
        $technologies = TechnologyModel::query()->count();
        $cards = PlanCardModel::query()->count();

        $this->confirmedDelete()->assertRedirect(self::PANEL_URL)->assertSessionHasNoErrors();

        $this->assertSame($zone->id, CoverageZoneModel::query()->sole()->id);
        $this->assertSame($plan->id, PlanModel::query()->sole()->id);
        $this->assertSame(1, ZonePlanPriceModel::query()->count());
        $this->assertSame(0, ZoneTechnologyModel::query()->count());
        $this->assertSame($technologies, TechnologyModel::query()->count());
        $this->assertSame($cards, PlanCardModel::query()->count());
    }

    public function test_after_deleting_the_query_no_longer_warns_about_sample_data(): void
    {
        $this->seed(CoverageSampleSeeder::class);

        $this->confirmedDelete();

        $this->postJson('/api/coverage/check', [
            'latitude' => 20.0,
            'longitude' => -100.0,
            'accuracy_meters' => 30,
            'address_confirmed' => true,
        ])->assertOk()->assertJsonPath('scenario', 'D')->assertJsonPath('sample_data', false);
    }

    public function test_sample_records_in_real_zones_and_plans_go_too(): void
    {
        $zone = $this->realZone();
        $plan = $this->realPlan();
        $this->realPrice($zone, $plan, sample: true);

        $this->confirmedDelete()->assertSessionHasNoErrors();

        $this->assertSame(0, ZonePlanPriceModel::query()->count());
        $this->assertNotNull($zone->fresh());
        $this->assertNotNull($plan->fresh());
    }

    public function test_an_edited_sample_record_does_not_block_and_goes_with_the_rest(): void
    {
        $this->seed(CoverageSampleSeeder::class);
        $zone = $this->sampleZone();
        $price = ZonePlanPriceModel::query()->where('zone_id', $zone->id)->firstOrFail();

        $this->patch("/panel/zonas/{$zone->id}", ['name' => 'Renombrada']);
        $this->post("/panel/zonas/{$zone->id}/tecnologias", ['technology' => 'fiber', 'status' => 'unavailable']);
        $this->post("/panel/zonas/{$zone->id}/precios", [
            'plan_id' => (string) $price->plan_id,
            'price' => '999',
            'installation_cost' => '0',
        ]);
        $this->patch("/panel/planes/{$price->plan_id}", ['technology' => 'fiber', 'name' => 'Otro', 'speed' => '10']);

        $this->assertSame(99900, $price->fresh()->price_cents);

        $this->confirmedDelete()->assertSessionHasNoErrors();

        $this->assertSame(0, CoverageZoneModel::query()->count());
        $this->assertSame(0, PlanModel::query()->count());
        $this->assertSame(0, ZonePlanPriceModel::query()->count());
    }

    public function test_a_real_price_in_a_sample_zone_blocks_and_nothing_is_deleted(): void
    {
        $this->seed(CoverageSampleSeeder::class);
        $this->realPrice($this->sampleZone(), $this->realPlan('Fibra Real'));

        $this->assertBlocked('Fibra Real', 'elimina la zona de ejemplo');
    }

    public function test_a_real_status_in_a_sample_zone_blocks_and_nothing_is_deleted(): void
    {
        $this->seed(CoverageSampleSeeder::class);
        $this->post("/panel/zonas/{$this->sampleZone()->id}/tecnologias", ['technology' => 'antenna', 'status' => 'available']);

        $this->assertBlocked('Ejemplo Fibra', 'Elimina la zona de ejemplo');
    }

    public function test_a_real_price_in_a_sample_plan_blocks_and_nothing_is_deleted(): void
    {
        $this->seed(CoverageSampleSeeder::class);
        $plan = PlanModel::query()->where('is_sample', true)->firstOrFail();
        $this->realPrice($this->realZone('Zona Real'), $plan);

        $this->assertBlocked('Zona Real', 'elimina el plan de ejemplo');
    }

    public function test_a_sample_zone_replaced_with_a_kml_blocks_and_nothing_is_deleted(): void
    {
        $this->seed(CoverageSampleSeeder::class);
        $this->sampleZone()->update(['boundary_replaced' => true]);

        $this->assertBlocked('Ejemplo Fibra', 'KML');
    }

    public function test_deleting_the_blocking_zone_by_hand_lets_the_cleanup_run(): void
    {
        $this->seed(CoverageSampleSeeder::class);
        $zone = $this->sampleZone();
        $this->realPrice($zone, $this->realPlan());

        $this->delete("/panel/zonas/{$zone->id}", ['confirmation' => '1']);

        $this->confirmedDelete()->assertSessionHasNoErrors();

        $this->assertSame(0, CoverageZoneModel::query()->count());
        $this->assertSame(1, PlanModel::query()->count());
    }

    private function assertBlocked(string $record, string $correction): void
    {
        $zones = CoverageZoneModel::query()->count();
        $plans = PlanModel::query()->count();
        $statuses = ZoneTechnologyModel::query()->count();
        $prices = ZonePlanPriceModel::query()->count();

        $this->from(self::PANEL_URL)
            ->followingRedirects()
            ->delete(self::DELETE_URL, ['confirmation' => '1'])
            ->assertOk()
            ->assertSee('No se borró nada')
            ->assertSee($record)
            ->assertSee($correction);

        $this->assertSame($zones, CoverageZoneModel::query()->count());
        $this->assertSame($plans, PlanModel::query()->count());
        $this->assertSame($statuses, ZoneTechnologyModel::query()->count());
        $this->assertSame($prices, ZonePlanPriceModel::query()->count());
    }

    private function confirmedDelete(): TestResponse
    {
        return $this->from(self::PANEL_URL)->delete(self::DELETE_URL, ['confirmation' => '1']);
    }

    private function sampleZone(): CoverageZoneModel
    {
        return CoverageZoneModel::query()->where('name', 'Ejemplo Fibra')->firstOrFail();
    }

    private function technologyId(string $code): int
    {
        return (int) TechnologyModel::query()->where('code', $code)->value('id');
    }

    private function realZone(string $name = 'Zona Real'): CoverageZoneModel
    {
        return CoverageZoneModel::query()->create([
            'name' => $name,
            'boundary' => [[[21.1, -101.1], [21.1, -101.0], [21.2, -101.0], [21.2, -101.1]]],
        ]);
    }

    private function realPlan(string $name = 'Fibra Real'): PlanModel
    {
        return PlanModel::query()->create([
            'technology_id' => $this->technologyId('fiber'),
            'name' => $name,
            'speed_mbps' => 100,
        ]);
    }

    private function realPrice(CoverageZoneModel $zone, PlanModel $plan, bool $sample = false): void
    {
        ZonePlanPriceModel::query()->create([
            'zone_id' => $zone->id,
            'plan_id' => $plan->id,
            'price_cents' => 45000,
            'installation_cost_cents' => 0,
            'is_sample' => $sample,
        ]);
    }
}
