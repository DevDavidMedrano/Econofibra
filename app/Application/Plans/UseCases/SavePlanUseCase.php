<?php

declare(strict_types=1);

namespace App\Application\Plans\UseCases;

use App\Domain\Coverage\ValueObjects\TechnologyType;
use App\Domain\Plans\Repositories\PlanCatalogRepository;
use App\Domain\Plans\ValueObjects\PlanName;
use App\Domain\Plans\ValueObjects\PlanSpeed;
use App\Domain\Shared\AdminChangeRejected;
use InvalidArgumentException;

final class SavePlanUseCase
{
    public function __construct(private PlanCatalogRepository $plans) {}

    /**
     * Creates the plan, or edits it when $planId is given.
     *
     * @throws AdminChangeRejected
     */
    public function execute(?int $planId, TechnologyType $technology, string $name, int $speedMbps): void
    {
        try {
            $planName = new PlanName($name);
            $speed = new PlanSpeed($speedMbps);
        } catch (InvalidArgumentException $invalid) {
            throw new AdminChangeRejected($invalid->getMessage(), previous: $invalid);
        }

        if ($planId === null) {
            $this->plans->create($technology, $planName, $speed);

            return;
        }

        $this->plans->update($planId, $technology, $planName, $speed);
    }
}
