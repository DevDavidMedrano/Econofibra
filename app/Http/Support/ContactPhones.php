<?php

declare(strict_types=1);

namespace App\Http\Support;

/**
 * Turns the configured phones into call links. Anything but a 10-digit number is left out.
 */
final class ContactPhones
{
    private const COUNTRY_CODE = '+52';

    /**
     * @return list<array{label: string, href: string}>
     */
    public static function configured(?string $phones): array
    {
        $numbers = array_map(trim(...), explode(',', (string) $phones));
        $valid = array_filter($numbers, fn (string $number): bool => preg_match('/^\d{10}$/', $number) === 1);

        return array_values(array_map(self::link(...), $valid));
    }

    /**
     * @return array{label: string, href: string}
     */
    private static function link(string $number): array
    {
        return [
            'label' => sprintf('%s %s %s', substr($number, 0, 3), substr($number, 3, 3), substr($number, 6)),
            'href' => 'tel:'.self::COUNTRY_CODE.$number,
        ];
    }
}
