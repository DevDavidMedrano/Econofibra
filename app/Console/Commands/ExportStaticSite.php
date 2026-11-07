<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

/**
 * Writes the single public page as plain HTML, so they can be hosted without PHP or a database.
 */
final class ExportStaticSite extends Command
{
    private const PAGES = ['/'];

    private const PUBLIC_FILES = ['build', 'images', 'favicon.ico', 'robots.txt'];

    protected $signature = 'site:export {url : Dirección pública del sitio} {--path= : Carpeta de salida}';

    protected $description = 'Exporta la página pública como HTML estático';

    public function handle(Kernel $kernel, Filesystem $files): int
    {
        if (! config('site.static')) {
            $this->error('Activa SITE_STATIC=true antes de exportar.');

            return self::FAILURE;
        }

        $target = $this->option('path') ?: base_path('dist');
        $url = rtrim((string) $this->argument('url'), '/');

        $files->deleteDirectory($target);
        $files->ensureDirectoryExists($target);

        URL::forceRootUrl($url);
        URL::forceScheme('https');

        foreach (self::PAGES as $page) {
            $response = $kernel->handle(Request::create($page));

            if ($response->getStatusCode() !== 200) {
                $this->error("{$page} respondió {$response->getStatusCode()}.");

                return self::FAILURE;
            }

            $folder = rtrim($target.$page, '/');
            $files->ensureDirectoryExists($folder);
            $files->put($folder.'/index.html', (string) $response->getContent());
        }

        foreach (self::PUBLIC_FILES as $name) {
            $source = public_path($name);

            if ($files->isDirectory($source)) {
                $files->copyDirectory($source, $target.'/'.$name);
            } elseif ($files->exists($source)) {
                $files->copy($source, $target.'/'.$name);
            }
        }

        // Vite escribe rutas desde la raíz, que fallan si el sitio vive en una subcarpeta.
        foreach ($files->glob($target.'/build/assets/*.css') as $stylesheet) {
            $files->put($stylesheet, str_replace('url(/build/assets/', 'url(./', $files->get($stylesheet)));
        }

        $files->put($target.'/.nojekyll', '');
        $this->info("Sitio exportado en {$target}.");

        return self::SUCCESS;
    }
}
