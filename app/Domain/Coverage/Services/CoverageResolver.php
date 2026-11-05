<?php

declare(strict_types=1);

namespace App\Domain\Coverage\Services;

use App\Domain\Coverage\Entities\CoverageResult;
use App\Domain\Coverage\Entities\CoverageZone;
use App\Domain\Coverage\Entities\TechnologyOffer;
use App\Domain\Coverage\ValueObjects\CoverageReason;
use App\Domain\Coverage\ValueObjects\CoverageScenario;
use App\Domain\Coverage\ValueObjects\TechnologyStatus;
use App\Domain\Coverage\ValueObjects\TechnologyType;
use App\Domain\Plans\Entities\InternetPlan;
use App\Domain\Plans\Services\PlanCompletenessPolicy;

final class CoverageResolver
{
    public function __construct(
        private PlanCompletenessPolicy $completeness,
        private TechnologyRecommender $recommender,
    ) {}

    public function lowPrecision(): CoverageResult
    {
        return CoverageResult::technicalReview(CoverageReason::LowPrecision);
    }

    public function ambiguous(): CoverageResult
    {
        return CoverageResult::technicalReview(CoverageReason::AmbiguousLocation);
    }

    /**
     * Steps 3 to 5 of RF-13. A technology without a registered status may be missing
     * from the offers, and it counts as unavailable.
     *
     * @param  list<TechnologyOffer>  $offers
     */
    public function resolve(?CoverageZone $zone, array $offers): CoverageResult
    {
        if ($zone === null || $this->everyTechnologyIsUnavailable($offers)) {
            return CoverageResult::noCoverage($zone);
        }

        $confirmed = $this->confirmedOffers($offers);
        $types = array_map(static fn (TechnologyOffer $offer): TechnologyType => $offer->type, $confirmed);
        $recommended = $this->recommender->recommend($types);

        if ($recommended === null) {
            return CoverageResult::technicalReview(CoverageReason::PendingReview, $zone);
        }

        return CoverageResult::offered($this->scenarioFor($types), $zone, $confirmed, $recommended);
    }

    /** @param list<TechnologyOffer> $offers */
    private function everyTechnologyIsUnavailable(array $offers): bool
    {
        foreach ($offers as $offer) {
            if ($offer->status !== TechnologyStatus::Unavailable) {
                return false;
            }
        }

        return true;
    }

    /**
     * Confirmed offers with only their complete plans, fiber first.
     *
     * @param  list<TechnologyOffer>  $offers
     * @return list<TechnologyOffer>
     */
    private function confirmedOffers(array $offers): array
    {
        $confirmed = [];

        foreach ($offers as $offer) {
            $plans = array_values(array_filter(
                $offer->plans,
                fn (InternetPlan $plan): bool => $this->completeness->isComplete($plan),
            ));

            if ($offer->status === TechnologyStatus::Available && $plans !== []) {
                $confirmed[] = $offer->withPlans($plans);
            }
        }

        usort(
            $confirmed,
            static fn (TechnologyOffer $a, TechnologyOffer $b): int => ($b->type === TechnologyType::Fiber) <=> ($a->type === TechnologyType::Fiber),
        );

        return $confirmed;
    }

    /** @param list<TechnologyType> $types */
    private function scenarioFor(array $types): CoverageScenario
    {
        $hasFiber = in_array(TechnologyType::Fiber, $types, true);
        $hasAntenna = in_array(TechnologyType::Antenna, $types, true);

        return match (true) {
            $hasFiber && $hasAntenna => CoverageScenario::C,
            $hasFiber => CoverageScenario::A,
            default => CoverageScenario::B,
        };
    }
}
