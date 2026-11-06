<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Admin\Repositories\AdminAccountRepository;
use App\Domain\Admin\ValueObjects\AdminEmail;
use App\Domain\Admin\ValueObjects\AdminPassword;
use App\Infrastructure\Persistence\Eloquent\AdminUserModel;

final class EloquentAdminAccountRepository implements AdminAccountRepository
{
    private const ACCOUNT_NAME = 'Administrador';

    public function currentEmail(): ?string
    {
        return $this->account()?->email;
    }

    public function save(AdminEmail $email, AdminPassword $password): void
    {
        $account = $this->account() ?? new AdminUserModel(['name' => self::ACCOUNT_NAME]);

        $account->email = $email->value;
        $account->password = $password->value;
        $account->save();
    }

    private function account(): ?AdminUserModel
    {
        return AdminUserModel::query()->orderBy('id')->first();
    }
}
