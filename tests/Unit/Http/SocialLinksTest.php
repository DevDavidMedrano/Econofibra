<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use App\Http\Support\SocialLinks;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SocialLinksTest extends TestCase
{
    public function test_it_keeps_https_addresses_with_a_domain_and_their_keys(): void
    {
        $urls = [
            'facebook' => 'https://www.facebook.com/econofibra',
            'youtube' => 'https://youtube.com/@econofibra',
        ];

        $this->assertSame($urls, SocialLinks::configured($urls));
    }

    public function test_it_keeps_only_the_configured_network(): void
    {
        $this->assertSame(
            ['youtube' => 'https://youtube.com/@econofibra'],
            SocialLinks::configured(['facebook' => '', 'youtube' => 'https://youtube.com/@econofibra']),
        );
    }

    #[DataProvider('notConfigured')]
    public function test_it_drops_an_address_without_the_required_form(mixed $url): void
    {
        $this->assertSame([], SocialLinks::configured(['facebook' => $url]));
    }

    /** @return array<string, array{mixed}> */
    public static function notConfigured(): array
    {
        return [
            'nula' => [null],
            'vacía' => [''],
            'solo espacios' => ['   '],
            'http' => ['http://facebook.com/econofibra'],
            'sin esquema' => ['facebook.com/econofibra'],
            'javascript' => ['javascript:alert(1)'],
            'sin host' => ['https://'],
            'host sin dominio' => ['https://facebook'],
            'host con punto final vacío' => ['https://facebook.'],
            'host con espacio' => ['https://face book.com'],
            'no es texto' => [123],
        ];
    }
}
