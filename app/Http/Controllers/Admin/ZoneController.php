<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Coverage\UseCases\DeleteZoneUseCase;
use App\Application\Coverage\UseCases\ListZonesUseCase;
use App\Application\Coverage\UseCases\RenameZoneUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ConfirmedActionRequest;
use App\Http\Requests\Admin\RenameZoneRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

final class ZoneController extends Controller
{
    public function index(ListZonesUseCase $zones): View
    {
        return view('pages.admin.zones.index', ['zones' => $zones->execute()]);
    }

    public function update(RenameZoneRequest $request, RenameZoneUseCase $rename, int $zone): RedirectResponse
    {
        $rename->execute($zone, $request->string('name')->value());

        return redirect()->route('admin.zones.index')->with('status', 'Zona renombrada.');
    }

    public function destroy(ConfirmedActionRequest $request, DeleteZoneUseCase $delete, int $zone): RedirectResponse
    {
        $delete->execute($zone);

        return redirect()->route('admin.zones.index')->with('status', 'Zona eliminada.');
    }
}
