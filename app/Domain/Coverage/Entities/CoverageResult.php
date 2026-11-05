<?php

declare(strict_types=1);

namespace App\Domain\Coverage\Entities;

use App\Domain\Coverage\ValueObjects\CoverageReason;
use App\Domain\Coverage\ValueObjects\CoverageScenario;
use App\Domain\Coverage\ValueObjects\TechnologyType;

/**
 * Only an offered result can carry plans or a recommendation, so Scenarios D and E
 * cannot show them by construction.
 */
final readonly class CoverageResult
{
    /** @param list<TechnologyOffer> $offers */
    private function __construct(
        public CoverageScenario $scenario,
        public ?CoverageReason $reason,
        public ?CoverageZone $zone,
        public array $offers,
        public ?TechnologyType $recommended,
    ) {}

    /** @param list<TechnologyOffer> $offers */
    public static function offered(
        CoverageScenario $scenario,
        CoverageZone $zone,
        array $offers,
        TechnologyType $recommended,
    ): self {
        return new self($scenario, null, $zone, $offers, $recommended);
    }

    public static function noCoverage(?CoverageZone $zone): self
    {
        return new self(CoverageScenario::D, null, $zone, [], null);
    }

    public static function technicalReview(CoverageReason $reason, ?CoverageZone $zone = null): self
    {
        return new self(CoverageScenario::E, $reason, $zone, [], null);
    }
}
