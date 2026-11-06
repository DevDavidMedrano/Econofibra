<?php

declare(strict_types=1);

namespace App\Domain\Admin\Repositories;

use App\Domain\Admin\ValueObjects\AdminEmail;
use App\Domain\Admin\ValueObjects\AdminPassword;

interface AdminAccountRepository
{
    /** The email of the only account, or null when it does not exist yet. */
    public function currentEmail(): ?string;

    /** Creates the only account or overwrites it, and stores the password in an unreadable form. */
    public function save(AdminEmail $email, AdminPassword $password): void;
}
