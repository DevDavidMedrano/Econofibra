<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Application\Admin\UseCases\SetAdminAccountUseCase;
use Illuminate\Console\Command;
use InvalidArgumentException;

final class SetAdminAccountCommand extends Command
{
    protected $signature = 'admin:account {correo : Correo de la cuenta del panel}';

    protected $description = 'Crea la cuenta del panel o restablece su contraseña';

    public function handle(SetAdminAccountUseCase $setAccount): int
    {
        // Without a fallback, a terminal that cannot hide the input fails instead of showing the password.
        $password = (string) $this->secret('Contraseña (mínimo 12 caracteres)', false);

        try {
            $result = $setAccount->execute((string) $this->argument('correo'), $password);
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if ($result->replacedEmail !== null) {
            $this->warn("El correo de la cuenta cambió de {$result->replacedEmail} a {$result->email}.");
        }

        $this->info($result->created
            ? "Cuenta creada para {$result->email}."
            : "Contraseña restablecida para {$result->email}. Las sesiones abiertas quedan cerradas.");

        return self::SUCCESS;
    }
}
