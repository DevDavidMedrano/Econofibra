<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Infrastructure\Persistence\Eloquent\AdminUserModel;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

final class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    private const EMAIL = 'admin@example.com';

    private const PASSWORD = 'clave-de-prueba-larga';

    private const WRONG_CREDENTIALS = 'Correo o contraseña incorrectos.';

    private const LOCKED = 'Demasiados intentos. Vuelve a intentarlo más tarde';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->travelTo(CarbonImmutable::parse('2026-11-05 10:00:00', 'UTC'));

        AdminUserModel::query()->create([
            'name' => 'Administrador',
            'email' => self::EMAIL,
            'password' => self::PASSWORD,
        ]);
    }

    public function test_public_pages_do_not_link_to_the_panel(): void
    {
        $this->get('/')->assertOk()->assertDontSee('/panel', false);
        $this->get('/cobertura')->assertOk()->assertDontSee('/panel', false);
    }

    public function test_there_is_no_public_registration_or_password_recovery(): void
    {
        foreach (Route::getRoutes() as $route) {
            $this->assertDoesNotMatchRegularExpression('/regist|recuper|forgot|reset/i', $route->uri());
        }

        $this->get('/panel/registro')->assertNotFound();
    }

    public function test_the_panel_sends_a_guest_to_the_login_without_showing_data(): void
    {
        $this->get('/panel')
            ->assertRedirect('/panel/entrar')
            ->assertDontSee('Administración');
    }

    public function test_the_login_page_is_shown_to_a_guest(): void
    {
        $this->get('/panel/entrar')->assertOk()->assertViewIs('pages.admin.login');
    }

    public function test_valid_credentials_open_the_panel(): void
    {
        $this->login()->assertRedirect('/panel');

        $this->assertAuthenticated();
        $this->nextRequest();
        $this->get('/panel')->assertOk()->assertViewIs('pages.admin.dashboard');
    }

    public function test_a_logged_in_administrator_skips_the_login_page(): void
    {
        $this->login();
        $this->nextRequest();

        $this->get('/panel/entrar')->assertRedirect('/panel');
    }

    public function test_the_email_is_compared_without_case_or_outer_spaces(): void
    {
        $this->login(email: '  ADMIN@Example.com ')->assertRedirect('/panel');

        $this->assertAuthenticated();
    }

    public function test_wrong_password_unknown_email_and_bad_format_give_the_same_message(): void
    {
        foreach ([
            [self::EMAIL, 'contraseña-equivocada'],
            ['otro@example.com', self::PASSWORD],
            ['esto no es un correo', self::PASSWORD],
            ['', ''],
        ] as [$email, $password]) {
            $this->login($email, $password)->assertSessionHasErrors(['email' => self::WRONG_CREDENTIALS]);
            $this->assertGuest();
        }
    }

    public function test_the_fifth_failure_blocks_even_the_right_password_until_minute_15(): void
    {
        $this->failLogins(5);

        $this->travelTo(now()->addSeconds(14 * 60 + 59));
        $this->login()->assertSessionHasErrors(['email' => self::LOCKED]);
        $this->assertGuest();

        $this->travelTo(now()->addSecond());
        $this->login()->assertRedirect('/panel');
        $this->assertAuthenticated();
    }

    public function test_four_failures_do_not_block(): void
    {
        $this->failLogins(4);

        $this->login()->assertRedirect('/panel');
    }

    public function test_the_block_lasts_15_minutes_from_the_fifth_failure_and_not_from_the_first(): void
    {
        $this->failLogins(4);
        $this->travelTo(now()->addMinutes(14));
        $this->failLogins(1);

        $this->travelTo(now()->addSeconds(14 * 60 + 59));
        $this->login()->assertSessionHasErrors(['email' => self::LOCKED]);

        $this->travelTo(now()->addSecond());
        $this->login()->assertRedirect('/panel');
    }

    public function test_consecutive_failures_count_even_when_they_are_far_apart(): void
    {
        $this->failLogins(4);
        $this->travelTo(now()->addHours(2));
        $this->failLogins(1);

        $this->login()->assertSessionHasErrors(['email' => self::LOCKED]);
    }

    public function test_attempts_during_the_block_do_not_extend_it(): void
    {
        $this->failLogins(5);
        $this->travelTo(now()->addMinutes(14));
        $this->login(password: 'otra-equivocada')->assertSessionHasErrors(['email' => self::LOCKED]);

        $this->travelTo(now()->addMinute());
        $this->login()->assertRedirect('/panel');
    }

    public function test_attempts_are_counted_without_case_differences(): void
    {
        foreach (['ADMIN@example.com', 'Admin@Example.com', 'admin@EXAMPLE.com', ' admin@example.com', 'ADMIN@EXAMPLE.COM'] as $email) {
            $this->login($email, 'contraseña-equivocada');
        }

        $this->login()->assertSessionHasErrors(['email' => self::LOCKED]);
    }

    public function test_the_block_applies_to_one_email_from_one_network_address(): void
    {
        $this->failLogins(5);

        $this->login('otro@example.com', 'contraseña-equivocada')
            ->assertSessionHasErrors(['email' => self::WRONG_CREDENTIALS]);

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.9']);
        $this->login()->assertRedirect('/panel');
    }

    public function test_a_successful_login_restarts_the_count(): void
    {
        $this->failLogins(4);
        $this->login();
        $this->post('/panel/salir');
        $this->nextRequest();

        $this->failLogins(4);

        $this->login()->assertRedirect('/panel');
    }

    public function test_after_the_block_the_count_starts_again(): void
    {
        $this->failLogins(5);
        $this->travelTo(now()->addMinutes(15));

        $this->failLogins(1);

        $this->login()->assertRedirect('/panel');
    }

    public function test_logging_out_ends_the_session(): void
    {
        $this->login();
        $this->nextRequest();

        $this->post('/panel/salir')->assertRedirect('/panel/entrar');

        $this->assertGuest();
        $this->nextRequest();
        $this->get('/panel')->assertRedirect('/panel/entrar');
    }

    public function test_the_session_survives_29_minutes_59_seconds_without_activity(): void
    {
        $this->login();
        $this->travelTo(now()->addSeconds(29 * 60 + 59));
        $this->nextRequest();

        $this->get('/panel')->assertOk();
    }

    public function test_the_session_ends_after_30_minutes_without_activity(): void
    {
        $this->login();
        $this->travelTo(now()->addMinutes(30));
        $this->nextRequest();

        $this->get('/panel')->assertRedirect('/panel/entrar');

        $this->assertGuest();
    }

    public function test_activity_restarts_the_idle_time(): void
    {
        $this->login();
        $this->travelTo(now()->addMinutes(20));
        $this->nextRequest();
        $this->get('/panel')->assertOk();

        $this->travelTo(now()->addMinutes(20));
        $this->nextRequest();

        $this->get('/panel')->assertOk();
    }

    public function test_a_change_on_an_idle_session_is_not_applied(): void
    {
        $this->login();
        $this->travelTo(now()->addMinutes(30));
        $this->nextRequest();

        $this->post('/panel/salir')->assertRedirect('/panel/entrar')->assertSessionHas('status');

        $this->assertGuest();
    }

    public function test_a_forged_change_with_a_session_is_rejected(): void
    {
        $this->login();
        $this->nextRequest();
        $this->enableCsrfProtection();

        $this->post('/panel/salir', [], ['Sec-Fetch-Site' => 'cross-site'])->assertStatus(419);
        $this->post('/panel/salir', ['_token' => 'token-falso'])->assertStatus(419);

        $this->assertAuthenticated();
    }

    public function test_a_change_from_the_panel_form_is_accepted(): void
    {
        $this->login();
        $this->nextRequest();
        $this->enableCsrfProtection();

        $this->post('/panel/salir', ['_token' => session()->token()])->assertRedirect('/panel/entrar');

        $this->assertGuest();
    }

    public function test_a_change_without_a_session_and_with_an_old_token_goes_to_the_login(): void
    {
        $this->enableCsrfProtection();

        $this->post('/panel/salir', ['_token' => 'token-vencido'])
            ->assertRedirect('/panel/entrar')
            ->assertSessionHas('status');
        $this->post('/panel/entrar', ['_token' => 'token-vencido'])->assertRedirect('/panel/entrar');
    }

    public function test_a_forged_login_is_rejected_before_checking_the_credentials(): void
    {
        $this->enableCsrfProtection();

        $this->login()->assertRedirect('/panel/entrar');

        $this->assertGuest();
    }

    public function test_a_public_post_with_a_bad_token_keeps_the_419(): void
    {
        Route::post('/formulario-publico', fn () => 'ok')->middleware('web');
        $this->enableCsrfProtection();

        $this->post('/formulario-publico')->assertStatus(419);
    }

    private function login(string $email = self::EMAIL, string $password = self::PASSWORD): TestResponse
    {
        return $this->post('/panel/entrar', ['email' => $email, 'password' => $password]);
    }

    private function failLogins(int $times): void
    {
        for ($attempt = 0; $attempt < $times; $attempt++) {
            $this->login(password: 'contraseña-equivocada');
        }
    }

    /** The guard keeps its user in memory between test requests; a real request starts without it. */
    private function nextRequest(): void
    {
        $this->app['auth']->forgetGuards();
    }

    /** Laravel skips this protection in the testing environment. */
    private function enableCsrfProtection(): void
    {
        $this->app['env'] = 'local';
    }
}
