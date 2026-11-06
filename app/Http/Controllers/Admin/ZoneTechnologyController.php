<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Coverage\UseCases\SetZoneTechnologyStatusUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SetZoneTechnologyStatusRequest;
use Illuminate\Http\RedirectResponse;

final class ZoneTechnologyController extends Controller
{
    public function __invoke(
        SetZoneTechnologyStatusRequest $request,
        SetZoneTechnologyStatusUseCase $setStatus,
        int $zone,
    ): RedirectResponse {
        $setStatus->execute($zone, $request->technology(), $request->status());

        return redirect()->route('admin.zones.index')->with('status', 'Estado guardado.');
    }
}
