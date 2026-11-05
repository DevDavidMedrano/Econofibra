<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Coverage;

use App\Domain\Coverage\Entities\CoverageResult;
use App\Domain\Coverage\Entities\CoverageZone;
use App\Domain\Coverage\Entities\TechnologyOffer;
use App\Domain\Coverage\Services\CoverageResolver;
use App\Domain\Coverage\Services\TechnologyRecommender;
use App\Domain\Coverage\ValueObjects\CoverageReason;
use App\Domain\Coverage\ValueObjects\CoverageScenario;
use App\Domain\Coverage\ValueObjects\GeoPoint;
use App\Domain\Coverage\ValueObjects\TechnologyStatus;
use App\Domain\Coverage\ValueObjects\TechnologyType;
use App\Domain\Coverage\ValueObjects\ZoneBoundary;
use App\Domain\Plans\Entities\InternetPlan;
use App\Domain\Plans\Services\PlanCompletenessPolicy;
use App\Domain\Plans\ValueObjects\Money;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CoverageResolverTest extends TestCase
{
    public function test_confirmed_fiber_alone_is_scenario_a(): void
    {
        $plans = [$this->plan('Fibra 50'), $this->plan('Fibra 100')];

        $result = $this->resolve([$this->fiber(TechnologyStatus::Available, $plans)]);

        $this->assertSame(CoverageScenario::A, $result->scenario);
        $this->assertNull($result->reason);
        $this->assertSame('Zona', $result->zone?->name);
        $this->assertSame(TechnologyType::Fiber, $result->recommended);
        $this->assertSame([TechnologyType::Fiber], $this->types($result));
        $this->assertSame($plans, $result->offers[0]->plans);
    }

    public function test_confirmed_antenna_alone_is_scenario_b(): void
    {
        $result = $this->resolve([$this->antenna(TechnologyStatus::Available, [$this->plan('Antena 20')])]);

        $this->assertSame(CoverageScenario::B, $result->scenario);
        $this->assertNull($result->reason);
        $this->assertSame(TechnologyType::Antenna, $result->recommended);
        $this->assertSame([TechnologyType::Antenna], $this->types($result));
    }

    public function test_both_confirmed_is_scenario_c_with_fiber_recommended(): void
    {
        $result = $this->resolve([
            $this->fiber(TechnologyStatus::Available, [$this->plan('Fibra 100')]),
            $this->antenna(TechnologyStatus::Available, [$this->plan('Antena 20')]),
        ]);

        $this->assertSame(CoverageScenario::C, $result->scenario);
        $this->assertNull($result->reason);
        $this->assertSame(TechnologyType::Fiber, $result->recommended);
    }

    public function test_scenario_c_lists_fiber_first_whatever_the_order_received(): void
    {
        $result = $this->resolve([
            $this->antenna(TechnologyStatus::Available, [$this->plan('Antena 20')]),
            $this->fiber(TechnologyStatus::Available, [$this->plan('Fibra 100')]),
        ]);

        $this->assertSame([TechnologyType::Fiber, TechnologyType::Antenna], $this->types($result));
    }

    public function test_confirmed_fiber_with_a_pending_antenna_is_scenario_a_without_the_antenna(): void
    {
        $result = $this->resolve([
            $this->fiber(TechnologyStatus::Available, [$this->plan('Fibra 100')]),
            $this->antenna(TechnologyStatus::PendingReview, [$this->plan('Antena 20')]),
        ]);

        $this->assertSame(CoverageScenario::A, $result->scenario);
        $this->assertSame([TechnologyType::Fiber], $this->types($result));
    }

    public function test_confirmed_antenna_with_pending_fiber_is_scenario_b_with_the_antenna_recommended(): void
    {
        $result = $this->resolve([
            $this->fiber(TechnologyStatus::PendingReview, [$this->plan('Fibra 100')]),
            $this->antenna(TechnologyStatus::Available, [$this->plan('Antena 20')]),
        ]);

        $this->assertSame(CoverageScenario::B, $result->scenario);
        $this->assertSame(TechnologyType::Antenna, $result->recommended);
        $this->assertSame([TechnologyType::Antenna], $this->types($result));
    }

    public function test_an_unavailable_technology_is_not_offered_even_with_complete_plans(): void
    {
        $result = $this->resolve([
            $this->fiber(TechnologyStatus::Unavailable, [$this->plan('Fibra 100')]),
            $this->antenna(TechnologyStatus::Available, [$this->plan('Antena 20')]),
        ]);

        $this->assertSame(CoverageScenario::B, $result->scenario);
        $this->assertSame(TechnologyType::Antenna, $result->recommended);
        $this->assertSame([TechnologyType::Antenna], $this->types($result));
    }

    public function test_fiber_without_complete_plans_is_not_confirmed_next_to_a_confirmed_antenna(): void
    {
        $result = $this->resolve([
            $this->fiber(TechnologyStatus::Available, [$this->plan('Fibra 100', price: null)]),
            $this->antenna(TechnologyStatus::Available, [$this->plan('Antena 20')]),
        ]);

        $this->assertSame(CoverageScenario::B, $result->scenario);
        $this->assertSame(TechnologyType::Antenna, $result->recommended);
    }

    /** @param array<string, mixed> $values */
    #[DataProvider('incompleteValues')]
    public function test_only_the_complete_plans_are_offered(array $values): void
    {
        $complete = $this->plan('Fibra 100');
        $incomplete = $this->plan(...['name' => 'Fibra 50', ...$values]);

        $result = $this->resolve([$this->fiber(TechnologyStatus::Available, [$incomplete, $complete])]);

        $this->assertSame(CoverageScenario::A, $result->scenario);
        $this->assertSame([$complete], $result->offers[0]->plans);
    }

    /** @param array<string, mixed> $values */
    #[DataProvider('incompleteValues')]
    public function test_a_technology_left_without_complete_plans_is_scenario_e(array $values): void
    {
        $plan = $this->plan(...['name' => 'Fibra 50', ...$values]);

        $result = $this->resolve([$this->fiber(TechnologyStatus::Available, [$plan])]);

        $this->assertIsTechnicalReview($result, CoverageReason::PendingReview);
    }

    /** @return array<string, array{array<string, mixed>}> */
    public static function incompleteValues(): array
    {
        return [
            'without name' => [['name' => null]],
            'without speed' => [['speed' => null]],
            'without price' => [['price' => null]],
            'price of zero' => [['price' => 0]],
            'negative price' => [['price' => -100]],
            'without installation cost' => [['installation' => null]],
            'negative installation cost' => [['installation' => -100]],
        ];
    }

    public function test_a_plan_with_an_installation_cost_of_zero_is_offered(): void
    {
        $plan = $this->plan('Fibra 100', installation: 0);

        $result = $this->resolve([$this->fiber(TechnologyStatus::Available, [$plan])]);

        $this->assertSame(CoverageScenario::A, $result->scenario);
        $this->assertSame([$plan], $result->offers[0]->plans);
    }

    public function test_a_location_without_zone_is_scenario_d_without_zone(): void
    {
        $result = $this->resolver()->resolve(null, []);

        $this->assertIsNoCoverage($result);
        $this->assertNull($result->zone);
    }

    public function test_without_a_zone_the_offers_received_are_ignored(): void
    {
        $result = $this->resolver()->resolve(null, [
            $this->fiber(TechnologyStatus::Available, [$this->plan('Fibra 100')]),
        ]);

        $this->assertIsNoCoverage($result);
        $this->assertNull($result->zone);
    }

    public function test_a_zone_without_registered_technologies_is_scenario_d_with_zone(): void
    {
        $result = $this->resolve([]);

        $this->assertIsNoCoverage($result);
        $this->assertSame('Zona', $result->zone?->name);
    }

    public function test_a_zone_with_every_technology_unavailable_is_scenario_d_with_zone(): void
    {
        $result = $this->resolve([
            $this->fiber(TechnologyStatus::Unavailable, [$this->plan('Fibra 100')]),
            $this->antenna(TechnologyStatus::Unavailable, []),
        ]);

        $this->assertIsNoCoverage($result);
        $this->assertSame('Zona', $result->zone?->name);
    }

    public function test_pending_fiber_is_scenario_e_with_zone(): void
    {
        $result = $this->resolve([$this->fiber(TechnologyStatus::PendingReview, [$this->plan('Fibra 100')])]);

        $this->assertIsTechnicalReview($result, CoverageReason::PendingReview);
        $this->assertSame('Zona', $result->zone?->name);
    }

    public function test_both_technologies_pending_is_scenario_e(): void
    {
        $result = $this->resolve([
            $this->fiber(TechnologyStatus::PendingReview, []),
            $this->antenna(TechnologyStatus::PendingReview, [$this->plan('Antena 20')]),
        ]);

        $this->assertIsTechnicalReview($result, CoverageReason::PendingReview);
    }

    public function test_an_available_technology_without_plans_is_scenario_e(): void
    {
        $result = $this->resolve([$this->antenna(TechnologyStatus::Available, [])]);

        $this->assertIsTechnicalReview($result, CoverageReason::PendingReview);
    }

    public function test_a_pending_technology_next_to_an_unavailable_one_is_scenario_e(): void
    {
        $result = $this->resolve([
            $this->fiber(TechnologyStatus::Unavailable, []),
            $this->antenna(TechnologyStatus::PendingReview, []),
        ]);

        $this->assertIsTechnicalReview($result, CoverageReason::PendingReview);
    }

    public function test_low_precision_is_scenario_e_without_zone(): void
    {
        $result = $this->resolver()->lowPrecision();

        $this->assertIsTechnicalReview($result, CoverageReason::LowPrecision);
        $this->assertNull($result->zone);
    }

    public function test_an_ambiguous_location_is_scenario_e_without_zone(): void
    {
        $result = $this->resolver()->ambiguous();

        $this->assertIsTechnicalReview($result, CoverageReason::AmbiguousLocation);
        $this->assertNull($result->zone);
    }

    public function test_the_values_match_the_server_contract(): void
    {
        $this->assertSame(['A', 'B', 'C', 'D', 'E'], array_column(CoverageScenario::cases(), 'value'));
        $this->assertSame(
            ['low_precision', 'ambiguous_location', 'pending_review'],
            array_column(CoverageReason::cases(), 'value'),
        );
        $this->assertSame(['fiber', 'antenna'], array_column(TechnologyType::cases(), 'value'));
    }

    private function assertIsNoCoverage(CoverageResult $result): void
    {
        $this->assertSame(CoverageScenario::D, $result->scenario);
        $this->assertNull($result->reason);
        $this->assertSame([], $result->offers);
        $this->assertNull($result->recommended);
    }

    private function assertIsTechnicalReview(CoverageResult $result, CoverageReason $reason): void
    {
        $this->assertSame(CoverageScenario::E, $result->scenario);
        $this->assertSame($reason, $result->reason);
        $this->assertSame([], $result->offers);
        $this->assertNull($result->recommended);
    }

    /** @param list<TechnologyOffer> $offers */
    private function resolve(array $offers): CoverageResult
    {
        $zone = new CoverageZone(1, 'Zona', new ZoneBoundary([[
            new GeoPoint(19.995, -100.005),
            new GeoPoint(19.995, -99.995),
            new GeoPoint(20.005, -99.995),
            new GeoPoint(20.005, -100.005),
        ]]));

        return $this->resolver()->resolve($zone, $offers);
    }

    private function resolver(): CoverageResolver
    {
        return new CoverageResolver(new PlanCompletenessPolicy, new TechnologyRecommender);
    }

    /** @return list<TechnologyType> */
    private function types(CoverageResult $result): array
    {
        return array_map(static fn (TechnologyOffer $offer): TechnologyType => $offer->type, $result->offers);
    }

    /** @param list<InternetPlan> $plans */
    private function fiber(TechnologyStatus $status, array $plans): TechnologyOffer
    {
        return new TechnologyOffer(TechnologyType::Fiber, 'Fibra óptica', $status, $plans);
    }

    /** @param list<InternetPlan> $plans */
    private function antenna(TechnologyStatus $status, array $plans): TechnologyOffer
    {
        return new TechnologyOffer(TechnologyType::Antenna, 'Internet por antena', $status, $plans);
    }

    private function plan(?string $name, ?int $speed = 100, ?int $price = 49900, ?int $installation = 30000): InternetPlan
    {
        return new InternetPlan(
            $name,
            $speed,
            $price === null ? null : new Money($price),
            $installation === null ? null : new Money($installation),
        );
    }
}
