<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Plans\Entities\PlanCard;
use App\Domain\Plans\Repositories\PlanCardRepository;
use App\Domain\Plans\Repositories\PlanCardsUnavailable;
use App\Infrastructure\Persistence\Eloquent\PlanCardModel;
use Illuminate\Database\QueryException;
use PDOException;

final class EloquentPlanCardRepository implements PlanCardRepository
{
    public function all(): array
    {
        try {
            $models = PlanCardModel::query()->orderBy('id')->get();
        } catch (QueryException|PDOException $exception) {
            throw new PlanCardsUnavailable('The plan cards could not be read.', previous: $exception);
        }

        return $models
            ->map(static fn (PlanCardModel $model): PlanCard => new PlanCard(
                $model->id,
                $model->name,
                $model->speed_mbps,
                $model->phrase,
                $model->icon_key,
                $model->position,
                $model->is_popular,
            ))
            ->all();
    }
}
