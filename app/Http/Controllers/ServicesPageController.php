<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Support\SocialLinks;
use Illuminate\Contracts\View\View;

final class ServicesPageController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.services.index', [
            'coverageBarUrl' => route('coverage.page').'#barra-consulta',
            'socialLinks' => SocialLinks::configured(config('site.social')),
            'whatsappNumber' => config('coverage.whatsapp_number'),
        ]);
    }
}
