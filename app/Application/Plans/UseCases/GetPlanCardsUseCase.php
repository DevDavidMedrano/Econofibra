<?php

declare(strict_types=1);

namespace App\Application\Plans\UseCases;

use App\Domain\Plans\Entities\PlanCard;
use App\Domain\Plans\Repositories\PlanCardRepository;
use App\Domain\Plans\Repositories\PlanCardsUnavailable;
use App\Domain\Plans\Services\PlanCardCatalogPolicy;

/**
 * The cards to show. A read failure gives an empty list, so the rest of the page still works.
 */
final class GetPlanCardsUseCase
{
    public function __construct(
        private PlanCardRepository $cards,
        private PlanCardCatalogPolicy $policy,
    ) {}

    /** @return list<PlanCard> */
    public function execute(): array
    {
        try {
            return $this->policy->visibleCards($this->cards->all());
        } catch (PlanCardsUnavailable) {
            return [];
        }
    }
}
