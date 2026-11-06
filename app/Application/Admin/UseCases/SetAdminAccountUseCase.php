<?php

declare(strict_types=1);

namespace App\Application\Admin\UseCases;

use App\Application\Admin\DTOs\SetAdminAccountOutput;
use App\Domain\Admin\Repositories\AdminAccountRepository;
use App\Domain\Admin\ValueObjects\AdminEmail;
use App\Domain\Admin\ValueObjects\AdminPassword;
use InvalidArgumentException;
use SensitiveParameter;

final class SetAdminAccountUseCase
{
    public function __construct(private AdminAccountRepository $accounts) {}

    /** @throws InvalidArgumentException When the email or the password is not valid; nothing is saved. */
    public function execute(string $email, #[SensitiveParameter] string $password): SetAdminAccountOutput
    {
        $newEmail = new AdminEmail($email);
        $newPassword = new AdminPassword($password);
        $currentEmail = $this->accounts->currentEmail();

        $this->accounts->save($newEmail, $newPassword);

        return new SetAdminAccountOutput(
            created: $currentEmail === null,
            email: $newEmail->value,
            replacedEmail: $currentEmail !== $newEmail->value ? $currentEmail : null,
        );
    }
}
