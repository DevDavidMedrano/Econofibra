<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Plans;

use App\Domain\Plans\Entities\InternetPlan;
use App\Domain\Plans\Services\PlanCompletenessPolicy;
use App\Domain\Plans\ValueObjects\Money;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PlanCompletenessPolicyTest extends TestCase
{
    public function test_a_plan_with_every_value_is_complete(): void
    {
        $plan = new InternetPlan('Fibra 100', 100, new Money(49900), new Money(30000));

        $this->assertTrue((new PlanCompletenessPolicy)->isComplete($plan));
    }

    public function test_an_installation_cost_of_zero_is_complete(): void
    {
        $plan = new InternetPlan('Fibra 100', 100, new Money(49900), new Money(0));

        $this->assertTrue((new PlanCompletenessPolicy)->isComplete($plan));
    }

    public function test_the_smallest_valid_price_is_complete(): void
    {
        $plan = new InternetPlan('Fibra 100', 100, new Money(1), new Money(0));

        $this->assertTrue((new PlanCompletenessPolicy)->isComplete($plan));
    }

    #[DataProvider('incompletePlans')]
    public function test_an_incomplete_plan_is_rejected(InternetPlan $plan): void
    {
        $this->assertFalse((new PlanCompletenessPolicy)->isComplete($plan));
    }

    /** @return array<string, array{InternetPlan}> */
    public static function incompletePlans(): array
    {
        return [
            'without name' => [new InternetPlan(null, 100, new Money(49900), new Money(0))],
            'empty name' => [new InternetPlan('', 100, new Money(49900), new Money(0))],
            'blank name' => [new InternetPlan('   ', 100, new Money(49900), new Money(0))],
            'without speed' => [new InternetPlan('Fibra', null, new Money(49900), new Money(0))],
            'speed of zero' => [new InternetPlan('Fibra', 0, new Money(49900), new Money(0))],
            'negative speed' => [new InternetPlan('Fibra', -100, new Money(49900), new Money(0))],
            'without price' => [new InternetPlan('Fibra', 100, null, new Money(0))],
            'price of zero' => [new InternetPlan('Fibra', 100, new Money(0), new Money(0))],
            'negative price' => [new InternetPlan('Fibra', 100, new Money(-1), new Money(0))],
            'without installation cost' => [new InternetPlan('Fibra', 100, new Money(49900), null)],
            'negative installation cost' => [new InternetPlan('Fibra', 100, new Money(49900), new Money(-1))],
        ];
    }
}
