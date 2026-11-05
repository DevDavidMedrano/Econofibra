<?php

declare(strict_types=1);

namespace App\Domain\Plans\Services;

use App\Domain\Plans\Entities\InternetPlan;

final class PlanCompletenessPolicy
{
    public function isComplete(InternetPlan $plan): bool
    {
        return $plan->name !== null && trim($plan->name) !== ''
            && $plan->speedMbps !== null && $plan->speedMbps > 0
            && $plan->price !== null && $plan->price->isPositive()
            && $plan->installationCost !== null && ! $plan->installationCost->isNegative();
    }
}
