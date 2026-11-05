<?php

declare(strict_types=1);

namespace App\Domain\Coverage\Services;

use App\Domain\Coverage\ValueObjects\TechnologyType;

final class TechnologyRecommender
{
    /** @param list<TechnologyType> $confirmed */
    public function recommend(array $confirmed): ?TechnologyType
    {
        foreach ([TechnologyType::Fiber, TechnologyType::Antenna] as $type) {
            if (in_array($type, $confirmed, true)) {
                return $type;
            }
        }

        return null;
    }
}
