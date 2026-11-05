<?php

declare(strict_types=1);

namespace App\Application\Coverage\UseCases;

use App\Application\Coverage\DTOs\CheckCoverageInput;
use App\Application\Coverage\DTOs\CheckCoverageOutput;
use App\Domain\Coverage\Entities\CoverageResult;
use App\Domain\Coverage\Repositories\CoverageZoneRepository;
use App\Domain\Coverage\Repositories\SampleDataRepository;
use App\Domain\Coverage\Repositories\ZoneOfferRepository;
use App\Domain\Coverage\Services\CoverageResolver;
use App\Domain\Coverage\Services\PrecisionPolicy;
use App\Domain\Coverage\Services\ZoneLocator;
use App\Domain\Coverage\ValueObjects\GeoPoint;
use App\Domain\Coverage\ValueObjects\PrecisionMargin;

/**
 * Resolves a query in the order of RF-13. It keeps nothing: the location ends with the call.
 */
final class CheckCoverageUseCase
{
    public function __construct(
        private CoverageZoneRepository $zones,
        private ZoneOfferRepository $offers,
        private SampleDataRepository $sampleData,
        private PrecisionPolicy $precision,
        private ZoneLocator $locator,
        private CoverageResolver $resolver,
        private int $precisionThresholdMeters,
    ) {}

    public function execute(CheckCoverageInput $input): CheckCoverageOutput
    {
        $result = $this->resolve(
            new GeoPoint($input->latitude, $input->longitude),
            new PrecisionMargin($input->accuracyMeters),
        );

        return new CheckCoverageOutput($result, $this->sampleData->hasSampleData());
    }

    private function resolve(GeoPoint $point, PrecisionMargin $margin): CoverageResult
    {
        if ($this->precision->isLow($margin, $this->precisionThresholdMeters)) {
            return $this->resolver->lowPrecision();
        }

        $match = $this->locator->locate($point, $margin, $this->zones->all());

        if ($match->isAmbiguous()) {
            return $this->resolver->ambiguous();
        }

        $zone = $match->zone();

        return $this->resolver->resolve($zone, $zone === null ? [] : $this->offers->forZone($zone->id));
    }
}
