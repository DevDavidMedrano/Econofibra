<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Plans;

use App\Domain\Plans\ValueObjects\Money;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
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

    #[DataProvider('validAmounts')]
    public function test_a_decimal_text_becomes_whole_cents(string $text, int $cents): void
    {
        $this->assertSame($cents, Money::fromDecimalString($text)->cents);
    }

    /** @return array<string, array{string, int}> */
    public static function validAmounts(): array
    {
        return [
            'whole' => ['450', 45000],
            'one decimal' => ['450.5', 45050],
            'two decimals' => ['450.05', 45005],
            'a value that float rounding breaks' => ['4.35', 435],
            'zero' => ['0', 0],
            'maximum' => ['999999.99', 99999999],
        ];
    }

    #[DataProvider('invalidAmounts')]
    public function test_a_text_that_is_not_a_valid_amount_is_rejected(string $text): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::fromDecimalString($text);
    }

    /** @return array<string, array{string}> */
    public static function invalidAmounts(): array
    {
        return [
            'empty' => [''],
            'negative' => ['-1'],
            'too large' => ['1000000'],
            'three decimals' => ['1.234'],
            'comma' => ['1,50'],
            'no whole part' => ['.50'],
            'no decimals after the point' => ['5.'],
            'text' => ['abc'],
            'exponent' => ['1e3'],
            'trailing line break' => ["5\n"],
        ];
    }

    public function test_it_is_written_with_two_decimals(): void
    {
        $this->assertSame('450.05', (new Money(45005))->toDecimalString());
        $this->assertSame('0.00', (new Money(0))->toDecimalString());
        $this->assertSame('-0.01', (new Money(-1))->toDecimalString());
    }
}
