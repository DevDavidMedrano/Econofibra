<?php

declare(strict_types=1);

namespace Tests\Integration\Persistence;

use App\Domain\Coverage\Repositories\SampleDataRepository;
use App\Infrastructure\Persistence\Eloquent\PlanCardModel;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\PlanCardSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class PlanCardSeederTest extends TestCase
{
    use RefreshDatabase;

    private const INITIAL_CARDS = [
        ['Hogar Conectado', 50, 'Navega, estudia y disfruta', 'casa', 1, false],
        ['Gaming', 100, 'Juega sin lag', 'control', 2, false],
        ['Negocios', 200, 'Trabaja sin interrupciones', 'maletin', 3, false],
        ['Premium', 300, 'Máximo rendimiento', 'cohete', 4, true],
    ];

    public function test_the_database_seeder_creates_the_four_initial_cards_in_order(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(self::INITIAL_CARDS, $this->storedCards());
    }

    public function test_an_empty_table_gets_the_four_initial_cards(): void
    {
        $this->seed(PlanCardSeeder::class);
        PlanCardModel::query()->delete();

        $this->seed(PlanCardSeeder::class);

        $this->assertSame(self::INITIAL_CARDS, $this->storedCards());
    }

    public function test_seeding_twice_does_not_duplicate_the_cards(): void
    {
        $this->seed(PlanCardSeeder::class);
        $this->seed(PlanCardSeeder::class);

        $this->assertSame(self::INITIAL_CARDS, $this->storedCards());
    }

    public function test_seeding_again_leaves_the_edited_cards_as_they_are(): void
    {
        $this->seed(PlanCardSeeder::class);
        PlanCardModel::query()->where('name', 'Gaming')->update(['name' => 'Gamer', 'speed_mbps' => 150]);
        PlanCardModel::query()->where('name', 'Negocios')->delete();
        $edited = $this->storedCards();

        $this->seed(PlanCardSeeder::class);

        $this->assertSame($edited, $this->storedCards());
    }

    public function test_one_foreign_card_is_enough_to_add_nothing(): void
    {
        PlanCardModel::query()->create(['name' => 'Propia', 'speed_mbps' => 10]);

        $this->seed(PlanCardSeeder::class);

        $this->assertSame(['Propia'], PlanCardModel::query()->pluck('name')->all());
    }

    public function test_the_cards_have_no_sample_mark_and_no_relation_with_plans(): void
    {
        $columns = Schema::getColumnListing('plan_cards');

        $this->assertContains('icon_key', $columns);
        $this->assertNotContains('is_sample', $columns);
        $this->assertNotContains('plan_id', $columns);
        $this->assertSame([], Schema::getForeignKeys('plan_cards'));
    }

    public function test_the_cards_do_not_count_as_sample_data(): void
    {
        $this->seed(PlanCardSeeder::class);

        $this->assertFalse($this->app->make(SampleDataRepository::class)->hasSampleData());
    }

    public function test_changing_a_card_does_not_change_any_plan_of_any_zone(): void
    {
        $this->seed(DatabaseSeeder::class);
        $plans = DB::table('plans')->orderBy('id')->get();
        $prices = DB::table('zone_plan_prices')->orderBy('id')->get();

        PlanCardModel::query()->where('name', 'Gaming')->update(['speed_mbps' => 999]);
        PlanCardModel::query()->where('name', 'Premium')->delete();

        $this->assertEquals($plans, DB::table('plans')->orderBy('id')->get());
        $this->assertEquals($prices, DB::table('zone_plan_prices')->orderBy('id')->get());
    }

    public function test_removing_the_sample_data_keeps_the_cards(): void
    {
        $this->seed(DatabaseSeeder::class);

        foreach (['zone_plan_prices', 'zone_technologies', 'plans', 'coverage_zones'] as $table) {
            DB::table($table)->where('is_sample', true)->delete();
        }

        $this->assertSame(self::INITIAL_CARDS, $this->storedCards());
    }

    /** @return list<array{?string, ?int, ?string, ?string, ?int, bool}> */
    private function storedCards(): array
    {
        return PlanCardModel::query()
            ->orderBy('position')
            ->get()
            ->map(static fn (PlanCardModel $card): array => [
                $card->name,
                $card->speed_mbps,
                $card->phrase,
                $card->icon_key,
                $card->position,
                $card->is_popular,
            ])
            ->all();
    }
}
