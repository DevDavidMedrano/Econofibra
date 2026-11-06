<?php

declare(strict_types=1);

namespace App\Application\Admin\DTOs;

final readonly class SetAdminAccountOutput
{
    public function __construct(
        public bool $created,
        public string $email,
        public ?string $replacedEmail,
    ) {}
}
