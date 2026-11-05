<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Plans;

use App\Domain\Plans\ValueObjects\PlanCardIcon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PlanCardIconTest extends TestCase
{
    public function test_the_list_of_keys_is_closed(): void
    {
        $keys = array_map(static fn (PlanCardIcon $icon): string => $icon->value, PlanCardIcon::cases());

        $this->assertSame(['casa', 'control', 'maletin', 'cohete'], $keys);
    }

    #[DataProvider('unknownKeys')]
    public function test_a_key_outside_the_list_means_the_default_icon(string $key): void
    {
        $this->assertNull(PlanCardIcon::tryFrom($key));
    }

    /** @return array<string, array{string}> */
    public static function unknownKeys(): array
    {
        return [
            'unknown' => ['estrella'],
            'empty' => [''],
            'other case' => ['Casa'],
            'with spaces' => [' casa '],
        ];
    }
}
