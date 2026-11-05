<?php

declare(strict_types=1);

namespace Tests\Integration\Persistence;

use App\Application\Plans\UseCases\GetPlanCardsUseCase;
use App\Domain\Plans\Entities\PlanCard;
use App\Domain\Plans\Repositories\PlanCardRepository;
use App\Domain\Plans\Repositories\PlanCardsUnavailable;
use App\Infrastructure\Persistence\Eloquent\PlanCardModel;
use App\Infrastructure\Persistence\Repositories\EloquentPlanCardRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class EloquentPlanCardRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_contract_is_bound_to_the_eloquent_repository(): void
    {
        $this->assertInstanceOf(EloquentPlanCardRepository::class, $this->repository());
    }

    public function test_it_reads_every_value_of_a_card(): void
    {
        $model = $this->storeCard([
            'name' => 'Premium',
            'speed_mbps' => 300,
            'phrase' => 'Máximo rendimiento',
            'icon_key' => 'cohete',
            'position' => 4,
            'is_popular' => true,
        ]);

        $this->assertEquals(
            [new PlanCard($model->id, 'Premium', 300, 'Máximo rendimiento', 'cohete', 4, true)],
            $this->repository()->all(),
        );
    }

    public function test_it_reads_a_card_with_missing_values(): void
    {
        $model = $this->storeCard([
            'name' => null,
            'speed_mbps' => null,
            'phrase' => null,
            'icon_key' => null,
            'position' => null,
        ]);

        $this->assertEquals(
            [new PlanCard($model->id, null, null, null, null, null, false)],
            $this->repository()->all(),
        );
    }

    public function test_an_empty_table_gives_no_cards(): void
    {
        $this->assertSame([], $this->repository()->all());
    }

    public function test_a_read_failure_becomes_plan_cards_unavailable(): void
    {
        Schema::drop('plan_cards');

        $this->expectException(PlanCardsUnavailable::class);

        $this->repository()->all();
    }

    public function test_the_use_case_returns_the_complete_cards_in_order(): void
    {
        $this->storeCard(['name' => 'Segunda', 'position' => 2]);
        $this->storeCard(['name' => 'Sin frase', 'position' => 1, 'phrase' => '   ']);
        $this->storeCard(['name' => 'Sin posición', 'position' => null]);
        $this->storeCard(['name' => 'Primera', 'position' => 1]);

        $this->assertSame(['Primera', 'Segunda', 'Sin posición'], $this->visibleNames());
    }

    public function test_the_use_case_gives_no_cards_when_none_is_complete(): void
    {
        $this->storeCard(['speed_mbps' => 0]);

        $this->assertSame([], $this->visibleNames());
    }

    public function test_the_use_case_gives_no_cards_when_the_read_fails(): void
    {
        Schema::drop('plan_cards');

        $this->assertSame([], $this->visibleNames());
    }

    public function test_a_change_in_the_database_is_seen_in_the_next_read(): void
    {
        $model = $this->storeCard([
            'name' => 'Gaming',
            'speed_mbps' => 100,
            'phrase' => 'Juega sin lag',
            'icon_key' => 'control',
            'position' => 2,
            'is_popular' => false,
        ]);
        $this->assertSame(100, $this->useCase()->execute()[0]->speedMbps);

        $model->update([
            'name' => 'Gamer',
            'speed_mbps' => 150,
            'phrase' => 'Sin retrasos',
            'icon_key' => 'cohete',
            'position' => 9,
            'is_popular' => true,
        ]);

        $this->assertEquals(
            [new PlanCard($model->id, 'Gamer', 150, 'Sin retrasos', 'cohete', 9, true)],
            $this->useCase()->execute(),
        );
    }

    /** @param  array<string, mixed>  $values */
    private function storeCard(array $values): PlanCardModel
    {
        return PlanCardModel::query()->create($values + [
            'name' => 'Gaming',
            'speed_mbps' => 100,
            'phrase' => 'Juega sin lag',
            'icon_key' => 'control',
            'position' => 1,
        ]);
    }

    /** @return list<?string> */
    private function visibleNames(): array
    {
        return array_map(static fn (PlanCard $card): ?string => $card->name, $this->useCase()->execute());
    }

    private function repository(): PlanCardRepository
    {
        return $this->app->make(PlanCardRepository::class);
    }

    private function useCase(): GetPlanCardsUseCase
    {
        return $this->app->make(GetPlanCardsUseCase::class);
    }
}
