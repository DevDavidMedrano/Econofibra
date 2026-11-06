<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Plans;

use App\Domain\Plans\ValueObjects\PlanName;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class PlanNameTest extends TestCase
{
    public function test_the_spaces_at_the_ends_do_not_count(): void
    {
        $this->assertSame('Fibra 100', (new PlanName('  Fibra 100 '))->value);
        $this->assertSame(100, mb_strlen((new PlanName(' '.str_repeat('ñ', 100).' '))->value));
    }

    #[TestWith([''])]
    #[TestWith(['   '])]
    #[TestWith(["\xFF"])]
    public function test_a_text_that_is_not_valid_is_rejected(string $name): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PlanName($name);
    }

    public function test_a_name_of_more_than_100_characters_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PlanName(str_repeat('a', 101));
    }
}
