<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Plans;

use App\Domain\Plans\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function test_it_keeps_the_amount_in_whole_cents(): void
    {
        $this->assertSame(49900, (new Money(49900))->cents);
    }

    public function test_a_positive_amount(): void
    {
        $money = new Money(1);

        $this->assertTrue($money->isPositive());
        $this->assertFalse($money->isZero());
        $this->assertFalse($money->isNegative());
    }

    public function test_a_zero_amount(): void
    {
        $money = new Money(0);

        $this->assertFalse($money->isPositive());
        $this->assertTrue($money->isZero());
        $this->assertFalse($money->isNegative());
    }

    public function test_a_negative_amount_can_be_read_so_that_it_can_be_filtered_out(): void
    {
        $money = new Money(-1);

        $this->assertFalse($money->isPositive());
        $this->assertFalse($money->isZero());
        $this->assertTrue($money->isNegative());
    }
}
