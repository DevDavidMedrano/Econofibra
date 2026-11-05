<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

final class CoveragePageController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.coverage.index', [
            'locationTimeoutSeconds' => config('coverage.location_timeout_seconds'),
            'serverTimeoutSeconds' => config('coverage.server_timeout_seconds'),
            'whatsappNumber' => config('coverage.whatsapp_number'),
        ]);
    }
}
