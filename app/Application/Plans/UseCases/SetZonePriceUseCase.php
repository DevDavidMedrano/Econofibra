<?php

declare(strict_types=1);

namespace App\Application\Plans\UseCases;

use App\Domain\Plans\Repositories\PlanCatalogRepository;
use App\Domain\Plans\ValueObjects\Money;
use App\Domain\Shared\AdminChangeRejected;
use InvalidArgumentException;

final class SetZonePriceUseCase
{
    private const INVALID_PRICE = 'El precio debe ser mayor que cero, con hasta dos decimales y un máximo de 999999.99.';

    private const INVALID_INSTALLATION = 'El costo de instalación debe ser cero o más, con hasta dos decimales y un máximo de 999999.99.';

    public function __construct(private PlanCatalogRepository $plans) {}

    /** @throws AdminChangeRejected */
    public function execute(int $zoneId, int $planId, string $price, string $installationCost): void
    {
        $priceAmount = $this->amount($price, self::INVALID_PRICE);

        if (! $priceAmount->isPositive()) {
            throw new AdminChangeRejected(self::INVALID_PRICE);
        }

        $this->plans->setZonePrice(
            $zoneId,
            $planId,
            $priceAmount,
            $this->amount($installationCost, self::INVALID_INSTALLATION),
        );
    }

    private function amount(string $text, string $rejection): Money
    {
        try {
            return Money::fromDecimalString($text);
        } catch (InvalidArgumentException $invalid) {
            throw new AdminChangeRejected($rejection, previous: $invalid);
        }
    }
}
