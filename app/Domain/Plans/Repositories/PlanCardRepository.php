<?php

declare(strict_types=1);

namespace App\Domain\Plans\Repositories;

use App\Domain\Plans\Entities\PlanCard;

interface PlanCardRepository
{
    /**
     * Every stored card, complete or not.
     *
     * @return list<PlanCard>
     *
     * @throws PlanCardsUnavailable
     */
    public function all(): array;
}
