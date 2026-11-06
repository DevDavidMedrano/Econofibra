<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Infrastructure\Persistence\Eloquent\AdminUserModel;
use App\Infrastructure\Persistence\Eloquent\CoverageZoneModel;
use App\Infrastructure\Persistence\Eloquent\PlanModel;
use App\Infrastructure\Persistence\Eloquent\TechnologyModel;
use App\Infrastructure\Persistence\Eloquent\ZonePlanPriceModel;
use App\Infrastructure\Persistence\Eloquent\ZoneTechnologyModel;
use Database\Seeders\TechnologySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class PlanAdminTest extends TestCase
{
    use RefreshDatabase;

    private const PLANS_URL = '/panel/planes';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(TechnologySeeder::class);
        $this->actingAs(AdminUserModel::query()->create([
            'name' => 'Administrador',
            'email' => 'admin@example.com',
            'password' => 'clave-de-prueba-larga',
        ]));
    }

    public function test_every_plan_and_price_route_needs_a_session(): void
    {
        $zone = $this->zone();
        $plan = $this->plan();
        $this->app['auth']->guard()->logout();
        $this->app['auth']->forgetGuards();

        $this->get(self::PLANS_URL)->assertRedirect('/panel/entrar');
        $this->post(self::PLANS_URL, $this->planData())->assertRedirect('/panel/entrar');
        $this->patch(self::PLANS_URL."/{$plan->id}", $this->planData())->assertRedirect('/panel/entrar');
        $this->delete(self::PLANS_URL."/{$plan->id}", ['confirmation' => '1'])->assertRedirect('/panel/entrar');
        $this->get($this->pricesUrl($zone))->assertRedirect('/panel/entrar');
        $this->post($this->pricesUrl($zone), $this->priceData($plan))->assertRedirect('/panel/entrar');
        $this->delete($this->pricesUrl($zone)."/{$plan->id}")->assertRedirect('/panel/entrar');

        $this->assertSame(1, PlanModel::query()->count());
        $this->assertSame(0, ZonePlanPriceModel::query()->count());
    }

    public function test_the_list_shows_the_plans_and_how_many_zones_use_them(): void
    {
        $plan = $this->plan('Fibra <b>');
        $this->storedPrice($this->zone(), $plan);

        $this->get('/panel')->assertOk()->assertSee(self::PLANS_URL, false);

        $this->get(self::PLANS_URL)
            ->assertOk()
            ->assertViewIs('pages.admin.plans.index')
            ->assertSee('Fibra &lt;b&gt;', false)
            ->assertSee('Zonas con precio: 1');
    }

    public function test_a_plan_is_created_as_real(): void
    {
        $this->savePlan($this->planData(['name' => '  Fibra 200 ', 'speed' => '200']))->assertRedirect(self::PLANS_URL);

        $plan = PlanModel::query()->sole();
        $this->assertSame('Fibra 200', $plan->name);
        $this->assertSame(200, $plan->speed_mbps);
        $this->assertSame($this->technologyId('fiber'), $plan->technology_id);
        $this->assertFalse($plan->fresh()->is_sample);
    }

    /** @param array<string, mixed> $data */
    #[DataProvider('invalidPlans')]
    public function test_an_invalid_plan_is_rejected(array $data): void
    {
        $this->savePlan($this->planData($data))->assertRedirect(self::PLANS_URL)->assertSessionHasErrors();

        $this->assertSame(0, PlanModel::query()->count());
    }

    /** @return array<string, array{array<string, mixed>}> */
    public static function invalidPlans(): array
    {
        return [
            'empty name' => [['name' => '   ']],
            'name too long' => [['name' => str_repeat('a', 101)]],
            'name that is a list' => [['name' => ['a']]],
            'speed zero' => [['speed' => '0']],
            'speed over the maximum' => [['speed' => '100001']],
            'speed with decimals' => [['speed' => '10.5']],
            'speed as text' => [['speed' => 'rápido']],
            'unknown technology' => [['technology' => 'satellite']],
        ];
    }

    public function test_editing_a_sample_plan_keeps_its_mark(): void
    {
        $plan = $this->plan(sample: true);

        $this->patchPlan($plan->id, $this->planData(['name' => 'Fibra Nueva', 'speed' => '300']))
            ->assertRedirect(self::PLANS_URL);

        $plan->refresh();
        $this->assertSame('Fibra Nueva', $plan->name);
        $this->assertSame(300, $plan->speed_mbps);
        $this->assertTrue($plan->is_sample);
    }

    public function test_editing_a_plan_that_does_not_exist_is_rejected(): void
    {
        $this->patchPlan(999, $this->planData())->assertSessionHasErrors('admin');

        $this->assertSame(0, PlanModel::query()->count());
    }

    public function test_the_technology_of_a_plan_with_prices_cannot_change_and_nothing_is_saved(): void
    {
        $plan = $this->plan('Fibra 100');
        $this->storedPrice($this->zone(), $plan);

        $this->patchPlan($plan->id, $this->planData(['technology' => 'antenna', 'name' => 'Otro']))
            ->assertSessionHasErrors('admin');

        $plan->refresh();
        $this->assertSame($this->technologyId('fiber'), $plan->technology_id);
        $this->assertSame('Fibra 100', $plan->name);
    }

    public function test_a_plan_with_prices_can_change_its_name_and_speed(): void
    {
        $plan = $this->plan();
        $this->storedPrice($this->zone(), $plan);

        $this->patchPlan($plan->id, $this->planData(['name' => 'Otro', 'speed' => '500']))
            ->assertSessionHasNoErrors();

        $this->assertSame('Otro', $plan->fresh()->name);
    }

    public function test_the_technology_of_a_plan_without_prices_can_change(): void
    {
        $plan = $this->plan();

        $this->patchPlan($plan->id, $this->planData(['technology' => 'antenna']))->assertSessionHasNoErrors();

        $this->assertSame($this->technologyId('antenna'), $plan->fresh()->technology_id);
    }

    public function test_deleting_a_plan_needs_the_confirmation_field(): void
    {
        $plan = $this->plan();
        $this->storedPrice($this->zone(), $plan);

        $this->from(self::PLANS_URL)->delete(self::PLANS_URL."/{$plan->id}")
            ->assertSessionHasErrors('confirmation');

        $this->assertNotNull($plan->fresh());
        $this->assertSame(1, ZonePlanPriceModel::query()->count());
    }

    public function test_deleting_a_plan_removes_its_prices_in_every_zone_and_nothing_else(): void
    {
        $zone = $this->zone();
        $plan = $this->plan();
        $other = $this->plan('Otro');
        $this->storedPrice($zone, $plan);
        $this->storedPrice($this->zone('Sur'), $plan);
        $this->storedPrice($zone, $other);

        $this->from(self::PLANS_URL)->delete(self::PLANS_URL."/{$plan->id}", ['confirmation' => '1'])
            ->assertRedirect(self::PLANS_URL);

        $this->assertNull($plan->fresh());
        $this->assertSame($other->id, (int) ZonePlanPriceModel::query()->sole()->plan_id);
    }

    public function test_deleting_a_plan_that_does_not_exist_is_rejected(): void
    {
        $this->from(self::PLANS_URL)->delete(self::PLANS_URL.'/999', ['confirmation' => '1'])
            ->assertSessionHasErrors('admin');
    }

    public function test_the_prices_page_shows_the_assigned_plans_and_offers_the_others(): void
    {
        $zone = $this->zone('Centro <b>');
        $this->storedPrice($zone, $this->plan('Fibra 100'));
        $this->plan('Fibra 500');

        $this->get('/panel/zonas')->assertSee($this->pricesUrl($zone), false);

        $this->get($this->pricesUrl($zone))
            ->assertOk()
            ->assertViewIs('pages.admin.zones.prices')
            ->assertSee('Centro &lt;b&gt;', false)
            ->assertSee('Fibra 100')
            ->assertSee('value="450.00"', false)
            ->assertSee('Fibra 500');
    }

    public function test_the_prices_page_of_a_missing_zone_goes_back_to_the_list(): void
    {
        $this->get('/panel/zonas/999/precios')->assertRedirect('/panel/zonas')->assertSessionHasErrors('admin');
    }

    public function test_a_plan_is_assigned_to_a_zone_with_a_real_price(): void
    {
        $zone = $this->zone();
        $plan = $this->plan();

        $this->setPrice($zone->id, $this->priceData($plan, ['price' => '450.5', 'installation_cost' => '0']))
            ->assertRedirect($this->pricesUrl($zone));

        $price = ZonePlanPriceModel::query()->sole();
        $this->assertSame(45050, $price->price_cents);
        $this->assertSame(0, $price->installation_cost_cents);
        $this->assertFalse($price->is_sample);
    }

    public function test_changing_a_sample_price_keeps_its_mark(): void
    {
        $zone = $this->zone();
        $plan = $this->plan();
        $this->storedPrice($zone, $plan, sample: true);

        $this->setPrice($zone->id, $this->priceData($plan, ['price' => '500']));

        $price = ZonePlanPriceModel::query()->sole();
        $this->assertSame(50000, $price->price_cents);
        $this->assertTrue($price->is_sample);
    }

    /** @param array<string, mixed> $data */
    #[DataProvider('invalidAmounts')]
    public function test_an_invalid_amount_is_rejected_and_the_stored_one_stays(array $data): void
    {
        $zone = $this->zone();
        $plan = $this->plan();
        $this->storedPrice($zone, $plan);

        $this->setPrice($zone->id, $this->priceData($plan, $data))->assertSessionHasErrors();

        $price = ZonePlanPriceModel::query()->sole();
        $this->assertSame(45000, $price->price_cents);
        $this->assertSame(50000, $price->installation_cost_cents);
    }

    /** @return array<string, array{array<string, mixed>}> */
    public static function invalidAmounts(): array
    {
        return [
            'price zero' => [['price' => '0']],
            'price zero with decimals' => [['price' => '0.00']],
            'negative price' => [['price' => '-1']],
            'price over the maximum' => [['price' => '1000000']],
            'price with three decimals' => [['price' => '10.123']],
            'price as text' => [['price' => 'gratis']],
            'empty price' => [['price' => '']],
            'price that is a list' => [['price' => ['1']]],
            'negative installation' => [['installation_cost' => '-1']],
            'installation over the maximum' => [['installation_cost' => '1000000']],
            'empty installation' => [['installation_cost' => '']],
        ];
    }

    public function test_a_price_for_a_zone_that_does_not_exist_is_rejected(): void
    {
        $this->setPrice(999, $this->priceData($this->plan()))->assertSessionHasErrors('admin');

        $this->assertSame(0, ZonePlanPriceModel::query()->count());
    }

    public function test_a_price_for_a_plan_that_does_not_exist_is_rejected(): void
    {
        $zone = $this->zone();

        $this->setPrice($zone->id, ['plan_id' => '999', 'price' => '450', 'installation_cost' => '0'])
            ->assertSessionHasErrors('admin');

        $this->assertSame(0, ZonePlanPriceModel::query()->count());
    }

    public function test_a_plan_is_removed_from_one_zone_only(): void
    {
        $zone = $this->zone();
        $other = $this->zone('Sur');
        $plan = $this->plan();
        $this->storedPrice($zone, $plan);
        $this->storedPrice($other, $plan);

        $this->from($this->pricesUrl($zone))->delete($this->pricesUrl($zone)."/{$plan->id}")
            ->assertRedirect($this->pricesUrl($zone));

        $this->assertSame($other->id, (int) ZonePlanPriceModel::query()->sole()->zone_id);
        $this->assertNotNull($plan->fresh());
    }

    public function test_removing_a_price_that_does_not_exist_is_rejected(): void
    {
        $zone = $this->zone();

        $this->from($this->pricesUrl($zone))->delete($this->pricesUrl($zone).'/999')
            ->assertSessionHasErrors('admin');
    }

    public function test_a_price_changed_in_the_panel_appears_in_the_next_query(): void
    {
        $zone = $this->zone();
        $plan = $this->plan();
        $this->storedStatus($zone);
        $this->storedPrice($zone, $plan);

        $this->coverage()->assertJsonPath('technologies.0.plans.0.price_label', '$450.00/mes');

        $this->setPrice($zone->id, $this->priceData($plan, ['price' => '525.50']))->assertSessionHasNoErrors();

        $this->coverage()->assertJsonPath('technologies.0.plans.0.price_label', '$525.50/mes');
    }

    public function test_a_price_of_a_technology_that_is_not_available_is_saved_but_not_offered(): void
    {
        $zone = $this->zone();
        $plan = $this->plan();

        $this->setPrice($zone->id, $this->priceData($plan))->assertSessionHasNoErrors();

        $this->assertSame(1, ZonePlanPriceModel::query()->count());
        $this->coverage()->assertJsonPath('scenario', 'D')->assertJsonMissingPath('technologies.0.plans.0');
    }

    /** @param array<string, mixed> $data */
    private function savePlan(array $data): TestResponse
    {
        return $this->from(self::PLANS_URL)->post(self::PLANS_URL, $data);
    }

    /** @param array<string, mixed> $data */
    private function patchPlan(int $planId, array $data): TestResponse
    {
        return $this->from(self::PLANS_URL)->patch(self::PLANS_URL."/{$planId}", $data);
    }

    /** @param array<string, mixed> $data */
    private function setPrice(int $zoneId, array $data): TestResponse
    {
        return $this->from('/panel/zonas')->post("/panel/zonas/{$zoneId}/precios", $data);
    }

    private function pricesUrl(CoverageZoneModel $zone): string
    {
        return "/panel/zonas/{$zone->id}/precios";
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function planData(array $overrides = []): array
    {
        return [...['technology' => 'fiber', 'name' => 'Fibra 100', 'speed' => '100'], ...$overrides];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function priceData(PlanModel $plan, array $overrides = []): array
    {
        return [...['plan_id' => (string) $plan->id, 'price' => '450', 'installation_cost' => '500'], ...$overrides];
    }

    private function coverage(): TestResponse
    {
        return $this->postJson('/api/coverage/check', [
            'latitude' => 20.15,
            'longitude' => -100.05,
            'accuracy_meters' => 30,
            'address_confirmed' => true,
        ]);
    }

    private function technologyId(string $code): int
    {
        return (int) TechnologyModel::query()->where('code', $code)->value('id');
    }

    private function zone(string $name = 'Centro'): CoverageZoneModel
    {
        return CoverageZoneModel::query()->create([
            'name' => $name,
            'boundary' => [[[20.1, -100.1], [20.1, -100.0], [20.2, -100.0], [20.2, -100.1]]],
        ]);
    }

    private function plan(string $name = 'Fibra 100', bool $sample = false): PlanModel
    {
        return PlanModel::query()->create([
            'technology_id' => $this->technologyId('fiber'),
            'name' => $name,
            'speed_mbps' => 100,
            'is_sample' => $sample,
        ]);
    }

    private function storedStatus(CoverageZoneModel $zone): void
    {
        ZoneTechnologyModel::query()->create([
            'zone_id' => $zone->id,
            'technology_id' => $this->technologyId('fiber'),
            'status' => 'available',
        ]);
    }

    private function storedPrice(CoverageZoneModel $zone, PlanModel $plan, bool $sample = false): void
    {
        ZonePlanPriceModel::query()->create([
            'zone_id' => $zone->id,
            'plan_id' => $plan->id,
            'price_cents' => 45000,
            'installation_cost_cents' => 50000,
            'is_sample' => $sample,
        ]);
    }
}
