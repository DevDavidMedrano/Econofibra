<?php

use App\Domain\Shared\AdminChangeRejected;
use App\Http\Middleware\ForceHttps;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(ForceHttps::class);
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->dontReport(AdminChangeRejected::class);

        $exceptions->render(fn (AdminChangeRejected $rejected) => back(fallback: route('admin.dashboard'))
            ->withErrors(['admin' => $rejected->getMessage()]));

        // The CSRF check runs before auth, so a form left open past the session end gets a 419.
        // It uses respond() because Laravel converts this exception before the render() callbacks.
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            if ($response->getStatusCode() === 419 && $request->is('panel', 'panel/*') && $request->user() === null) {
                return redirect()->route('admin.login')->with('status', 'Tu sesión terminó. Entra de nuevo.');
            }

            return $response;
        });
    })->create();
