<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Plans\UseCases\GetPlanCardsUseCase;
use App\Http\Support\SocialLinks;
use Illuminate\Contracts\View\View;

final class HomePageController extends Controller
{
    public function __invoke(GetPlanCardsUseCase $getPlanCards): View
    {
        $shared = [
            'plansFromPrice' => config('site.plans_from_price'),
            'socialLinks' => SocialLinks::configured(config('site.social')),
            'whatsappNumber' => config('coverage.whatsapp_number'),
        ];

        if (config('site.static')) {
            return view('pages.home.single', $shared + [
                'planCards' => [],
                'coverageBarUrl' => '#cobertura',
                'locationTimeoutSeconds' => config('coverage.location_timeout_seconds'),
                'serverTimeoutSeconds' => config('coverage.server_timeout_seconds'),
            ]);
        }

        return view('pages.home.index', $shared + [
            'planCards' => $getPlanCards->execute(),
            'coverageBarUrl' => route('coverage.page').'#barra-consulta',
        ]);
    }
}
