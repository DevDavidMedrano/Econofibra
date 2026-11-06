<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Coverage\UseCases\ImportZonesUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportZonesRequest;
use Illuminate\Http\RedirectResponse;

final class ZoneImportController extends Controller
{
    public function __invoke(ImportZonesRequest $request, ImportZonesUseCase $import): RedirectResponse
    {
        $result = $import->execute($request->file('kml')->get());

        return redirect()->route('admin.zones.index')->with('import', [
            'created' => $result->created,
            'replaced' => $result->replaced,
            'ignored' => $result->ignoredGeometries,
        ]);
    }
}
