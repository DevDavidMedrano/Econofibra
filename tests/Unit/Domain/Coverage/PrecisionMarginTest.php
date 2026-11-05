<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Coverage;

use App\Domain\Coverage\ValueObjects\PrecisionMargin;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PrecisionMarginTest extends TestCase
{
    public function test_it_keeps_a_reported_margin(): void
    {
        $this->assertSame(35.5, (new PrecisionMargin(35.5))->meters);
    }

    public function test_an_absent_margin_has_no_meters(): void
    {
        $this->assertNull((new PrecisionMargin(null))->meters);
    }

    #[DataProvider('invalidMargins')]
    public function test_it_rejects_an_invalid_margin(float $meters): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PrecisionMargin($meters);
    }

    /** @return array<string, array{float}> */
    public static function invalidMargins(): array
    {
        return [
            'zero' => [0.0],
            'negative' => [-1.0],
            'not a number' => [NAN],
            'infinite' => [INF],
        ];
    }
}
