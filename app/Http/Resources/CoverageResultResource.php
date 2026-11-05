<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Application\Coverage\DTOs\CheckCoverageOutput;
use App\Domain\Coverage\Entities\CoverageResult;
use App\Domain\Coverage\Entities\TechnologyOffer;
use App\Domain\Plans\Entities\InternetPlan;
use App\Domain\Plans\ValueObjects\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read CheckCoverageOutput $resource
 */
final class CoverageResultResource extends JsonResource
{
    /** @var string|null */
    public static $wrap = null;

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $result = $this->resource->result;

        return [
            'scenario' => $result->scenario->value,
            'reason' => $result->reason?->value,
            'zone_name' => $result->zone?->name,
            'sample_data' => $this->resource->sampleData,
            'technologies' => array_map(
                fn (TechnologyOffer $offer): array => $this->technology($offer, $result),
                $result->offers,
            ),
        ];
    }

    /** @return array<string, mixed> */
    private function technology(TechnologyOffer $offer, CoverageResult $result): array
    {
        return [
            'code' => $offer->type->value,
            'name' => $offer->name,
            'recommended' => $offer->type === $result->recommended,
            'plans' => array_map($this->plan(...), $offer->plans),
        ];
    }

    /** @return array<string, mixed> */
    private function plan(InternetPlan $plan): array
    {
        return [
            'name' => $plan->name,
            'speed_mbps' => $plan->speedMbps,
            'price_label' => $this->amount($plan->price).config('coverage.price_suffix'),
            'installation_label' => $plan->installationCost->isZero()
                ? 'Instalación sin costo'
                : 'Instalación: '.$this->amount($plan->installationCost),
        ];
    }

    private function amount(Money $money): string
    {
        return config('coverage.currency_symbol').number_format($money->cents / 100, 2);
    }
}
