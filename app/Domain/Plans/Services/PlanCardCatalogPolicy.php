<?php

declare(strict_types=1);

namespace App\Domain\Plans\Services;

use App\Domain\Plans\Entities\PlanCard;

final class PlanCardCatalogPolicy
{
    /**
     * The complete cards by position, those without one last, then by name and creation order.
     *
     * @param  list<PlanCard>  $cards
     * @return list<PlanCard>
     */
    public function visibleCards(array $cards): array
    {
        $visible = array_values(array_filter($cards, $this->isComplete(...)));

        usort($visible, $this->compare(...));

        return $visible;
    }

    private function isComplete(PlanCard $card): bool
    {
        return $this->hasText($card->name)
            && $this->hasText($card->phrase)
            && $this->hasText($card->iconKey)
            && $card->speedMbps !== null && $card->speedMbps > 0;
    }

    private function hasText(?string $value): bool
    {
        return $value !== null && trim($value) !== '';
    }

    private function compare(PlanCard $first, PlanCard $second): int
    {
        return ($first->position === null) <=> ($second->position === null)
            ?: $first->position <=> $second->position
            ?: strcmp((string) $first->name, (string) $second->name)
            ?: $first->id <=> $second->id;
    }
}
