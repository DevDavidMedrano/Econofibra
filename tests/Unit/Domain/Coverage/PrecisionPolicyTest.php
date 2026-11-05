<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Coverage;

use App\Domain\Coverage\Services\PrecisionPolicy;
use App\Domain\Coverage\ValueObjects\PrecisionMargin;
use PHPUnit\Framework\TestCase;

final class PrecisionPolicyTest extends TestCase
{
    public function test_a_margin_equal_to_the_threshold_is_enough(): void
    {
        $this->assertFalse((new PrecisionPolicy)->isLow(new PrecisionMargin(100.0), 100));
    }

    public function test_a_margin_below_the_threshold_is_enough(): void
    {
        $this->assertFalse((new PrecisionPolicy)->isLow(new PrecisionMargin(30.0), 100));
    }

    public function test_a_margin_above_the_threshold_is_low(): void
    {
        $this->assertTrue((new PrecisionPolicy)->isLow(new PrecisionMargin(100.1), 100));
    }

    public function test_an_absent_margin_is_low(): void
    {
        $this->assertTrue((new PrecisionPolicy)->isLow(new PrecisionMargin(null), 100));
    }

    public function test_it_uses_the_threshold_it_receives(): void
    {
        $this->assertTrue((new PrecisionPolicy)->isLow(new PrecisionMargin(60.0), 50));
        $this->assertFalse((new PrecisionPolicy)->isLow(new PrecisionMargin(150.0), 200));
    }
}
