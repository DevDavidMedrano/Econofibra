<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class EndIdleAdminSession
{
    public const LAST_ACTIVITY = 'admin_last_activity';

    public function handle(Request $request, Closure $next): Response
    {
        $session = $request->session();
        $now = now()->getTimestamp();
        $lastActivity = $session->get(self::LAST_ACTIVITY);

        if (is_int($lastActivity) && $now - $lastActivity >= config('admin.idle_minutes') * 60) {
            Auth::logout();
            $session->invalidate();
            $session->regenerateToken();

            return redirect()->route('admin.login')->with('status', 'Tu sesión terminó. Entra de nuevo.');
        }

        $session->put(self::LAST_ACTIVITY, $now);

        return $next($request);
    }
}
