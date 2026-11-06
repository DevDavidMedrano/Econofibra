<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Support\SocialLinks;
use Illuminate\Contracts\View\View;

final class SupportPageController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.support.index', [
            'socialLinks' => SocialLinks::configured(config('site.social')),
            'whatsappNumber' => config('coverage.whatsapp_number'),
        ]);
    }
}
