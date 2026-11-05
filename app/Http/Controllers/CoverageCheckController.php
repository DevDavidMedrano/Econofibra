<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Coverage\UseCases\CheckCoverageUseCase;
use App\Http\Requests\CheckCoverageRequest;
use App\Http\Resources\CoverageResultResource;

final class CoverageCheckController extends Controller
{
    public function __invoke(CheckCoverageRequest $request, CheckCoverageUseCase $useCase): CoverageResultResource
    {
        return new CoverageResultResource($useCase->execute($request->toInput()));
    }
}
