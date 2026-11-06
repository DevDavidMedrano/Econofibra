<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Coverage;

use App\Domain\Coverage\Services\SampleCleanupPolicy;
use App\Domain\Coverage\ValueObjects\SampleBlocker;
use App\Domain\Coverage\ValueObjects\SampleSnapshot;
use PHPUnit\Framework\TestCase;

final class SampleCleanupPolicyTest extends TestCase
{
    public function test_sample_data_alone_blocks_nothing(): void
    {
        $this->assertSame([], $this->blockers(new SampleSnapshot(
            [$this->zone('Ejemplo', sample: true)],
            [$this->statusRow('Ejemplo', sample: true, zoneSample: true)],
            [$this->price('Ejemplo', 'Plan', sample: true, zoneSample: true, planSample: true)],
        )));
    }

    public function test_real_data_that_depends_on_no_sample_blocks_nothing(): void
    {
        $this->assertSame([], $this->blockers(new SampleSnapshot(
            [$this->zone('Real', sample: false, replaced: true)],
            [$this->statusRow('Real', sample: false, zoneSample: false)],
            [$this->price('Real', 'Plan', sample: false, zoneSample: false, planSample: false)],
        )));
    }

    public function test_sample_data_in_a_real_zone_or_plan_blocks_nothing(): void
    {
        $this->assertSame([], $this->blockers(new SampleSnapshot(
            [],
            [$this->statusRow('Real', sample: true, zoneSample: false)],
            [$this->price('Real', 'Plan', sample: true, zoneSample: false, planSample: false)],
        )));
    }

    public function test_a_real_price_in_a_sample_zone_blocks(): void
    {
        $blockers = $this->blockers(new SampleSnapshot([], [], [
            $this->price('Ejemplo Norte', 'Fibra 100', sample: false, zoneSample: true, planSample: false),
        ]));

        $this->assertCount(1, $blockers);
        $this->assertStringContainsString('Fibra 100', $blockers[0]->record);
        $this->assertStringContainsString('Ejemplo Norte', $blockers[0]->record);
        $this->assertStringContainsString('elimina la zona de ejemplo', $blockers[0]->correction);
    }

    public function test_a_real_price_in_a_sample_plan_blocks(): void
    {
        $blockers = $this->blockers(new SampleSnapshot([], [], [
            $this->price('Centro', 'Ejemplo Fibra 50', sample: false, zoneSample: false, planSample: true),
        ]));

        $this->assertCount(1, $blockers);
        $this->assertStringContainsString('Ejemplo Fibra 50', $blockers[0]->record);
        $this->assertStringContainsString('Centro', $blockers[0]->record);
        $this->assertStringContainsString('elimina el plan de ejemplo', $blockers[0]->correction);
    }

    public function test_a_real_price_in_a_sample_zone_and_a_sample_plan_is_listed_once(): void
    {
        $this->assertCount(1, $this->blockers(new SampleSnapshot([], [], [
            $this->price('Ejemplo', 'Ejemplo Plan', sample: false, zoneSample: true, planSample: true),
        ])));
    }

    public function test_a_real_status_in_a_sample_zone_blocks(): void
    {
        $blockers = $this->blockers(new SampleSnapshot([], [
            $this->statusRow('Ejemplo Norte', sample: false, zoneSample: true),
        ], []));

        $this->assertCount(1, $blockers);
        $this->assertStringContainsString('Fibra óptica', $blockers[0]->record);
        $this->assertStringContainsString('Ejemplo Norte', $blockers[0]->record);
        $this->assertStringContainsString('Elimina la zona de ejemplo', $blockers[0]->correction);
    }

    public function test_a_sample_zone_with_a_replaced_boundary_blocks(): void
    {
        $blockers = $this->blockers(new SampleSnapshot([
            $this->zone('Ejemplo Norte', sample: true, replaced: true),
        ], [], []));

        $this->assertCount(1, $blockers);
        $this->assertStringContainsString('Ejemplo Norte', $blockers[0]->record);
        $this->assertStringContainsString('KML', $blockers[0]->correction);
    }

    public function test_every_dependent_record_is_listed(): void
    {
        $this->assertCount(3, $this->blockers(new SampleSnapshot(
            [$this->zone('A', sample: true, replaced: true)],
            [$this->statusRow('A', sample: false, zoneSample: true)],
            [$this->price('A', 'Plan', sample: false, zoneSample: true, planSample: false)],
        )));
    }

    public function test_a_plan_without_a_name_can_still_be_named_in_the_message(): void
    {
        $blockers = $this->blockers(new SampleSnapshot([], [], [
            $this->price('Ejemplo', null, sample: false, zoneSample: true, planSample: false),
        ]));

        $this->assertStringContainsString('sin nombre', $blockers[0]->record);
    }

    /** @return list<SampleBlocker> */
    private function blockers(SampleSnapshot $snapshot): array
    {
        return (new SampleCleanupPolicy)->blockers($snapshot);
    }

    /** @return array{name: string, is_sample: bool, boundary_replaced: bool} */
    private function zone(string $name, bool $sample, bool $replaced = false): array
    {
        return ['name' => $name, 'is_sample' => $sample, 'boundary_replaced' => $replaced];
    }

    /** @return array{zone: string, technology: string, is_sample: bool, zone_is_sample: bool} */
    private function statusRow(string $zone, bool $sample, bool $zoneSample): array
    {
        return ['zone' => $zone, 'technology' => 'Fibra óptica', 'is_sample' => $sample, 'zone_is_sample' => $zoneSample];
    }

    /** @return array{zone: string, plan: ?string, is_sample: bool, zone_is_sample: bool, plan_is_sample: bool} */
    private function price(string $zone, ?string $plan, bool $sample, bool $zoneSample, bool $planSample): array
    {
        return [
            'zone' => $zone,
            'plan' => $plan,
            'is_sample' => $sample,
            'zone_is_sample' => $zoneSample,
            'plan_is_sample' => $planSample,
        ];
    }
}
