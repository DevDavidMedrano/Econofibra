<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Coverage;

use App\Domain\Coverage\ValueObjects\ZoneName;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ZoneNameTest extends TestCase
{
    public function test_it_removes_the_spaces_of_the_ends(): void
    {
        $this->assertSame('Centro Norte', (new ZoneName("  Centro Norte\n"))->value);
    }

    public function test_it_accepts_100_characters_counted_without_the_ends(): void
    {
        $name = str_repeat('ñ', 100);

        $this->assertSame($name, (new ZoneName("  {$name}  "))->value);
    }

    #[DataProvider('invalidNames')]
    public function test_it_rejects_a_text_that_is_not_valid(string $name): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ZoneName($name);
    }

    /** @return array<string, array{string}> */
    public static function invalidNames(): array
    {
        return [
            'empty' => [''],
            'only spaces' => ["  \n\t "],
            '101 characters' => [str_repeat('a', 101)],
            'broken encoding' => ["Centro \xFF"],
        ];
    }

    public function test_the_key_ignores_the_ends_and_the_case(): void
    {
        $this->assertSame((new ZoneName('Peñón Norte'))->key(), (new ZoneName('  PEÑÓN norte '))->key());
    }

    public function test_the_key_tells_accents_apart(): void
    {
        $this->assertNotSame((new ZoneName('Revisión'))->key(), (new ZoneName('Revision'))->key());
    }

    public function test_the_key_is_a_sha256_of_the_folded_name(): void
    {
        $this->assertSame(hash('sha256', 'ñandú'), (new ZoneName(' ÑANDÚ '))->key());
    }
}
