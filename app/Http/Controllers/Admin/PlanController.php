<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Plans\UseCases\DeletePlanUseCase;
use App\Application\Plans\UseCases\ListPlansUseCase;
use App\Application\Plans\UseCases\SavePlanUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ConfirmedActionRequest;
use App\Http\Requests\Admin\SavePlanRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

final class PlanController extends Controller
{
    public function index(ListPlansUseCase $plans): View
    {
        return view('pages.admin.plans.index', ['plans' => $plans->execute()]);
    }

    public function store(SavePlanRequest $request, SavePlanUseCase $save): RedirectResponse
    {
        $this->save($request, $save, null);

        return redirect()->route('admin.plans.index')->with('status', 'Plan creado.');
    }

    public function update(SavePlanRequest $request, SavePlanUseCase $save, int $plan): RedirectResponse
    {
        $this->save($request, $save, $plan);

        return redirect()->route('admin.plans.index')->with('status', 'Plan guardado.');
    }

    public function destroy(ConfirmedActionRequest $request, DeletePlanUseCase $delete, int $plan): RedirectResponse
    {
        $delete->execute($plan);

        return redirect()->route('admin.plans.index')->with('status', 'Plan eliminado.');
    }

    private function save(SavePlanRequest $request, SavePlanUseCase $save, ?int $planId): void
    {
        $save->execute($planId, $request->technology(), $request->string('name')->value(), $request->integer('speed'));
    }
}
