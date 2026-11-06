<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Plans;

use App\Domain\Plans\ValueObjects\PlanSpeed;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class PlanSpeedTest extends TestCase
{
    #[TestWith([1])]
    #[TestWith([100000])]
    public function test_the_limits_are_valid(int $mbps): void
    {
        $this->assertSame($mbps, (new PlanSpeed($mbps))->mbps);
    }

    #[TestWith([0])]
    #[TestWith([-1])]
    #[TestWith([100001])]
    public function test_a_speed_out_of_range_is_rejected(int $mbps): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PlanSpeed($mbps);
    }
}
