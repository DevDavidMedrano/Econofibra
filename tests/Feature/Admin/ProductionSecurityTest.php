<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Providers\AdminServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductionSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_a_get_over_http_is_redirected_permanently_with_its_path_and_query(): void
    {
        $this->inProduction();

        $this->get('http://localhost/cobertura?zona=1')
            ->assertStatus(301)
            ->assertRedirect('https://localhost/cobertura?zona=1');
    }

    public function test_a_head_over_http_is_redirected_permanently(): void
    {
        $this->inProduction();

        $this->call('HEAD', 'http://localhost/panel/entrar')
            ->assertStatus(301)
            ->assertRedirect('https://localhost/panel/entrar');
    }

    public function test_other_methods_keep_their_method_in_the_redirect(): void
    {
        $this->inProduction();

        $this->post('http://localhost/api/coverage/check')
            ->assertStatus(308)
            ->assertRedirect('https://localhost/api/coverage/check');
        $this->post('http://localhost/panel/entrar')->assertStatus(308);
    }

    public function test_a_path_that_looks_like_another_host_stays_on_the_site(): void
    {
        $this->inProduction();

        $this->get('http://localhost//malo.example/x')
            ->assertStatus(301)
            ->assertRedirect('https://localhost//malo.example/x');
    }

    public function test_a_request_over_https_is_served(): void
    {
        $this->inProduction();

        $this->get('https://localhost/panel/entrar')->assertOk();
    }

    public function test_http_is_served_outside_production(): void
    {
        $this->get('http://localhost/panel/entrar')->assertOk();
    }

    public function test_generated_links_use_https_in_production(): void
    {
        $this->inProduction();

        (new AdminServiceProvider($this->app))->boot();

        $this->assertStringStartsWith('https://', route('admin.login'));
    }

    public function test_https_reported_by_a_trusted_proxy_is_not_redirected(): void
    {
        $this->inProduction();
        config(['admin.trusted_proxies' => '10.0.0.1']);
        (new AdminServiceProvider($this->app))->boot();

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
            ->get('http://localhost/panel/entrar', ['X-Forwarded-Proto' => 'https'])
            ->assertOk();
    }

    public function test_https_reported_by_an_unknown_address_is_ignored(): void
    {
        $this->inProduction();
        config(['admin.trusted_proxies' => '10.0.0.1']);
        (new AdminServiceProvider($this->app))->boot();

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.2'])
            ->get('http://localhost/panel/entrar', ['X-Forwarded-Proto' => 'https'])
            ->assertStatus(301);
    }

    public function test_the_session_cookie_is_secure_httponly_and_lax_in_production(): void
    {
        $this->inProduction();
        $settings = $this->sessionSettings('production', null);
        config([
            'session.secure' => $settings['secure'],
            'session.http_only' => $settings['http_only'],
            'session.same_site' => $settings['same_site'],
        ]);

        $cookie = $this->get('https://localhost/panel/entrar')->getCookie(config('session.cookie'), false);

        $this->assertTrue($cookie->isSecure());
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertSame('lax', $cookie->getSameSite());
    }

    public function test_an_incomplete_environment_file_leaves_the_cookie_secure_in_production(): void
    {
        $this->assertTrue($this->sessionSettings('production', null)['secure']);
    }

    public function test_the_secure_attribute_follows_the_configuration_when_it_is_defined(): void
    {
        $this->assertFalse($this->sessionSettings('production', 'false')['secure']);
        $this->assertTrue($this->sessionSettings('local', 'true')['secure']);
    }

    public function test_the_cookie_is_not_forced_to_secure_outside_production(): void
    {
        $this->assertFalse($this->sessionSettings('local', null)['secure']);
    }

    private function inProduction(): void
    {
        $this->app['env'] = 'production';
    }

    /** @return array<string, mixed> The file config/session.php evaluated with these variables. */
    private function sessionSettings(string $appEnv, ?string $secureCookie): array
    {
        $values = ['APP_ENV' => $appEnv, 'SESSION_SECURE_COOKIE' => $secureCookie];
        $saved = [];

        foreach ($values as $name => $value) {
            $saved[$name] = [$_ENV[$name] ?? null, $_SERVER[$name] ?? null, getenv($name)];
            $this->setVariable($name, $value, $value, $value ?? false);
        }

        try {
            return require config_path('session.php');
        } finally {
            foreach ($saved as $name => [$env, $server, $process]) {
                $this->setVariable($name, $env, $server, $process);
            }
        }
    }

    private function setVariable(string $name, ?string $env, ?string $server, string|false $process): void
    {
        if ($env === null) {
            unset($_ENV[$name]);
        } else {
            $_ENV[$name] = $env;
        }

        if ($server === null) {
            unset($_SERVER[$name]);
        } else {
            $_SERVER[$name] = $server;
        }

        putenv($process === false ? $name : "{$name}={$process}");
    }
}
