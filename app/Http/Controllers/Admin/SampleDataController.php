<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Coverage\UseCases\DeleteSampleDataUseCase;
use App\Application\Coverage\UseCases\GetSampleSummaryUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ConfirmedActionRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

final class SampleDataController extends Controller
{
    public function index(GetSampleSummaryUseCase $summary): View
    {
        return view('pages.admin.dashboard', ['summary' => $summary->execute()]);
    }

    public function destroy(ConfirmedActionRequest $request, DeleteSampleDataUseCase $delete): RedirectResponse
    {
        $redirect = redirect()->route('admin.dashboard');

        // The page lists what must be corrected, read again from the stored data.
        return $delete->execute() === []
            ? $redirect->with('status', 'Datos de ejemplo borrados.')
            : $redirect->withErrors(['admin' => 'No se borró nada: hay datos reales que dependen de los de ejemplo.']);
    }
}
