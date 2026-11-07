<?php

declare(strict_types=1);

namespace Tests\Feature\Pages;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

final class StaticSiteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config(['site.static' => true]);
    }

    public function test_single_page_runs_no_database_query(): void
    {
        $queries = 0;
        DB::listen(function () use (&$queries): void {
            $queries++;
        });

        $this->get('/')->assertOk();

        $this->assertSame(0, $queries);
    }

    public function test_single_page_holds_every_section_and_links_them_from_the_menu(): void
    {
        $response = $this->get('/')->assertOk();

        foreach (['inicio', 'servicios', 'cobertura', 'soporte', 'contacto'] as $section) {
            $response->assertSee('id="'.$section.'"', false)->assertSee('href="#'.$section.'"', false);
        }

        $response->assertSee('Fibra Óptica')
            ->assertSee('Consulta tu cobertura')
            ->assertSee('¿Cómo podemos')
            ->assertSee('Redes inalámbricas')
            ->assertDontSee('24/7')
            ->assertSee('Escríbenos por WhatsApp');

        $this->assertSame(1, substr_count($response->getContent(), '<h1'));
    }

    public function test_single_page_leaves_whatsapp_buttons_to_a_single_listener(): void
    {
        $this->get('/')->assertDontSee('data-whatsapp-number=', false);
    }

    public function test_coverage_section_has_no_server_endpoint_and_explains_the_whatsapp_step(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('data-coverage-endpoint=""', false)
            ->assertSee('solo para preparar tu mensaje de WhatsApp')
            ->assertSee('Ya tenemos tu ubicación');
    }

    public function test_export_writes_the_single_page_under_the_public_url(): void
    {
        $target = storage_path('framework/testing/static-site');

        $this->artisan('site:export', ['url' => 'https://example.test/sitio', '--path' => $target])->assertSuccessful();

        $this->assertFileExists($target.'/index.html');
        $this->assertFileDoesNotExist($target.'/servicios/index.html');
        $this->assertStringContainsString('https://example.test/sitio/images/logo.jpeg', File::get($target.'/index.html'));

        File::deleteDirectory($target);
    }

    public function test_export_refuses_to_run_outside_static_mode(): void
    {
        config(['site.static' => false]);

        $this->artisan('site:export', ['url' => 'https://example.test'])->assertFailed();
    }
}
