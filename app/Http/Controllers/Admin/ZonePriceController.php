<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Plans\UseCases\ListZonePricesUseCase;
use App\Application\Plans\UseCases\RemoveZonePriceUseCase;
use App\Application\Plans\UseCases\SetZonePriceUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SetZonePriceRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

final class ZonePriceController extends Controller
{
    public function index(ListZonePricesUseCase $prices, int $zone): View|RedirectResponse
    {
        $sheet = $prices->execute($zone);

        if ($sheet === null) {
            return redirect()->route('admin.zones.index')->withErrors(['admin' => 'La zona ya no existe.']);
        }

        return view('pages.admin.zones.prices', ['sheet' => $sheet]);
    }

    public function store(SetZonePriceRequest $request, SetZonePriceUseCase $setPrice, int $zone): RedirectResponse
    {
        $setPrice->execute(
            $zone,
            $request->integer('plan_id'),
            $request->string('price')->value(),
            $request->string('installation_cost')->value(),
        );

        return redirect()->route('admin.zones.prices.index', $zone)->with('status', 'Precio guardado.');
    }

    public function destroy(RemoveZonePriceUseCase $remove, int $zone, int $plan): RedirectResponse
    {
        $remove->execute($zone, $plan);

        return redirect()->route('admin.zones.prices.index', $zone)->with('status', 'Plan quitado de la zona.');
    }
}
