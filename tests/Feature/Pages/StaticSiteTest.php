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

    public function test_public_pages_run_no_database_query(): void
    {
        $queries = 0;
        DB::listen(function () use (&$queries): void {
            $queries++;
        });

        foreach (['/', '/servicios', '/cobertura', '/soporte', '/contacto'] as $page) {
            $this->get($page)->assertOk();
        }

        $this->assertSame(0, $queries);
    }

    public function test_coverage_page_has_no_server_endpoint_and_explains_the_whatsapp_step(): void
    {
        $this->get('/cobertura')
            ->assertOk()
            ->assertSee('data-coverage-endpoint=""', false)
            ->assertSee('solo para preparar tu mensaje de WhatsApp')
            ->assertSee('Ya tenemos tu ubicación');
    }

    public function test_export_writes_every_page_under_the_public_url(): void
    {
        $target = storage_path('framework/testing/static-site');

        $this->artisan('site:export', ['url' => 'https://example.test/sitio', '--path' => $target])->assertSuccessful();

        foreach (['index.html', 'servicios/index.html', 'cobertura/index.html', 'soporte/index.html', 'contacto/index.html'] as $file) {
            $this->assertFileExists($target.'/'.$file);
        }

        $this->assertStringContainsString('https://example.test/sitio/cobertura', File::get($target.'/index.html'));

        File::deleteDirectory($target);
    }

    public function test_export_refuses_to_run_outside_static_mode(): void
    {
        config(['site.static' => false]);

        $this->artisan('site:export', ['url' => 'https://example.test'])->assertFailed();
    }
}
