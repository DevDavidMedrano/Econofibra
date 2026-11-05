<?php

declare(strict_types=1);

namespace App\Domain\Coverage\Entities;

use App\Domain\Coverage\ValueObjects\TechnologyStatus;
use App\Domain\Coverage\ValueObjects\TechnologyType;
use App\Domain\Plans\Entities\InternetPlan;

/**
 * What one technology offers in one zone: its status and the plans assigned there.
 */
final readonly class TechnologyOffer
{
    /** @param list<InternetPlan> $plans */
    public function __construct(
        public TechnologyType $type,
        public string $name,
        public TechnologyStatus $status,
        public array $plans,
    ) {}

    /** @param list<InternetPlan> $plans */
    public function withPlans(array $plans): self
    {
        return new self($this->type, $this->name, $this->status, $plans);
    }
}
