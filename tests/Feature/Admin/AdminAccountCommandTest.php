<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Infrastructure\Persistence\Eloquent\AdminUserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\PendingCommand;
use Tests\TestCase;

final class AdminAccountCommandTest extends TestCase
{
    use RefreshDatabase;

    private const QUESTION = 'Contraseña (mínimo 12 caracteres)';

    private const EMAIL = 'admin@example.com';

    private const PASSWORD = 'clave-de-prueba-larga';

    private const NEW_PASSWORD = 'otra-clave-de-prueba';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_it_creates_the_account_with_an_unreadable_password(): void
    {
        $this->setAccount(' Admin@Example.com ', self::PASSWORD)
            ->doesntExpectOutputToContain(self::PASSWORD)
            ->assertSuccessful()
            ->run();

        $account = AdminUserModel::query()->sole();
        $this->assertSame(self::EMAIL, $account->email);
        $this->assertSame('Administrador', $account->name);
        $this->assertNotSame(self::PASSWORD, $account->password);
        $this->assertTrue(Hash::check(self::PASSWORD, $account->password));
    }

    public function test_the_password_is_not_an_argument_or_an_option(): void
    {
        $definition = Artisan::all()['admin:account']->getDefinition();

        $this->assertSame(['correo'], array_keys($definition->getArguments()));
        $this->assertFalse($definition->hasOption('password'));
    }

    public function test_a_password_of_11_characters_is_rejected_without_creating_the_account(): void
    {
        $this->setAccount(self::EMAIL, '12345678901')->assertFailed()->run();

        $this->assertSame(0, AdminUserModel::query()->count());
    }

    public function test_a_password_of_12_characters_is_accepted(): void
    {
        $this->setAccount(self::EMAIL, '123456789012')->assertSuccessful()->run();

        $this->assertSame(1, AdminUserModel::query()->count());
    }

    public function test_an_invalid_email_is_rejected(): void
    {
        $this->setAccount('no-es-correo', self::PASSWORD)->assertFailed()->run();

        $this->assertSame(0, AdminUserModel::query()->count());
    }

    public function test_a_rejected_password_leaves_the_existing_account_as_it_was(): void
    {
        $this->setAccount(self::EMAIL, self::PASSWORD)->run();

        $this->setAccount('nuevo@example.com', 'corta')->assertFailed()->run();

        $account = AdminUserModel::query()->sole();
        $this->assertSame(self::EMAIL, $account->email);
        $this->assertTrue(Hash::check(self::PASSWORD, $account->password));
    }

    public function test_it_replaces_the_password_without_creating_a_second_account(): void
    {
        $this->setAccount(self::EMAIL, self::PASSWORD)->run();

        $this->setAccount(self::EMAIL, self::NEW_PASSWORD)->assertSuccessful()->run();

        $account = AdminUserModel::query()->sole();
        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $account->password));
        $this->assertFalse(Hash::check(self::PASSWORD, $account->password));
    }

    public function test_a_different_email_replaces_the_account_and_is_reported(): void
    {
        $this->setAccount(self::EMAIL, self::PASSWORD)->run();

        $this->setAccount('nuevo@example.com', self::NEW_PASSWORD)
            ->expectsOutputToContain('El correo de la cuenta cambió de admin@example.com a nuevo@example.com')
            ->assertSuccessful()
            ->run();

        $this->assertSame('nuevo@example.com', AdminUserModel::query()->sole()->email);

        $this->post('/panel/entrar', ['email' => self::EMAIL, 'password' => self::NEW_PASSWORD]);
        $this->assertGuest();

        $this->post('/panel/entrar', ['email' => 'nuevo@example.com', 'password' => self::NEW_PASSWORD])
            ->assertRedirect('/panel');
    }

    public function test_it_closes_a_session_that_already_used_the_panel(): void
    {
        $this->setAccount(self::EMAIL, self::PASSWORD)->run();
        $this->login();
        $this->nextRequest();
        $this->get('/panel')->assertOk();

        $this->setAccount(self::EMAIL, self::NEW_PASSWORD)->run();
        $this->nextRequest();

        $this->get('/panel')->assertRedirect('/panel/entrar');
        $this->assertGuest();
    }

    public function test_it_closes_a_session_that_has_not_opened_the_panel_yet(): void
    {
        $this->setAccount(self::EMAIL, self::PASSWORD)->run();
        $this->login();

        $this->setAccount(self::EMAIL, self::NEW_PASSWORD)->run();
        $this->nextRequest();

        $this->get('/panel')->assertRedirect('/panel/entrar');
    }

    public function test_it_closes_sessions_even_when_the_password_is_the_same(): void
    {
        $this->setAccount(self::EMAIL, self::PASSWORD)->run();
        $this->login();

        $this->setAccount(self::EMAIL, self::PASSWORD)->run();
        $this->nextRequest();

        $this->get('/panel')->assertRedirect('/panel/entrar');
    }

    private function setAccount(string $email, string $password): PendingCommand
    {
        return $this->artisan('admin:account', ['correo' => $email])
            ->expectsQuestion(self::QUESTION, $password);
    }

    private function login(): void
    {
        $this->post('/panel/entrar', ['email' => self::EMAIL, 'password' => self::PASSWORD])
            ->assertRedirect('/panel');
    }

    /** The guard keeps its user in memory between test requests; a real request starts without it. */
    private function nextRequest(): void
    {
        $this->app['auth']->forgetGuards();
    }
}
