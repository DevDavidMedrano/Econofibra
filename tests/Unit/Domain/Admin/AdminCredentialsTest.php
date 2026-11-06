<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Admin;

use App\Domain\Admin\ValueObjects\AdminEmail;
use App\Domain\Admin\ValueObjects\AdminPassword;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AdminCredentialsTest extends TestCase
{
    public function test_normalize_removes_outer_spaces_and_lowers_the_case(): void
    {
        $this->assertSame('dueño@example.com', AdminEmail::normalize("  DUEÑO@Example.COM \n"));
    }

    public function test_normalize_does_not_validate_the_format(): void
    {
        $this->assertSame('no es correo', AdminEmail::normalize(' No Es Correo '));
    }

    public function test_an_email_keeps_its_normalized_value(): void
    {
        $this->assertSame('admin@example.com', (new AdminEmail(' Admin@Example.com '))->value);
    }

    #[DataProvider('invalidEmails')]
    public function test_an_invalid_email_is_rejected(string $email): void
    {
        $this->expectException(InvalidArgumentException::class);

        new AdminEmail($email);
    }

    /** @return array<string, array{string}> */
    public static function invalidEmails(): array
    {
        return [
            'empty' => [''],
            'only spaces' => ['   '],
            'no at sign' => ['admin.example.com'],
            'no domain' => ['admin@'],
            'too long' => [str_repeat('a', 250).'@example.com'],
        ];
    }

    public function test_a_password_of_12_characters_is_accepted(): void
    {
        $this->assertSame('123456789012', (new AdminPassword('123456789012'))->value);
    }

    public function test_a_password_of_11_characters_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new AdminPassword('12345678901');
    }

    public function test_the_length_counts_characters_and_not_bytes(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new AdminPassword(str_repeat('ñ', 11));
    }

    public function test_the_rejection_does_not_reveal_the_password(): void
    {
        try {
            new AdminPassword('clave-corta');
            $this->fail('The password should be rejected.');
        } catch (InvalidArgumentException $exception) {
            $this->assertStringNotContainsString('clave-corta', $exception->getMessage());
        }
    }
}
