<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Appended to the global stack so it runs after TrustProxies: running before it would
 * redirect in a loop behind a proxy that ends HTTPS.
 */
final class ForceHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->isProduction() || $request->secure()) {
            return $next($request);
        }

        // Built by hand: redirect()->secure() would treat a path like //other.host as a full URL.
        return redirect()->away(
            'https://'.$request->getHttpHost().$request->getRequestUri(),
            $request->isMethod('GET') || $request->isMethod('HEAD') ? 301 : 308,
        );
    }
}
