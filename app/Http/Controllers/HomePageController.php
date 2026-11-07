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
        return view('pages.home.index', [
            'planCards' => config('site.static') ? [] : $getPlanCards->execute(),
            'plansFromPrice' => config('site.plans_from_price'),
            'coverageBarUrl' => route('coverage.page').'#barra-consulta',
            'socialLinks' => SocialLinks::configured(config('site.social')),
            'whatsappNumber' => config('coverage.whatsapp_number'),
        ]);
    }
}
