<?php

declare(strict_types=1);

namespace App\Application\Coverage\UseCases;

use App\Domain\Coverage\Repositories\ZoneCatalogRepository;
use App\Domain\Coverage\ValueObjects\ZoneName;
use App\Domain\Shared\AdminChangeRejected;
use InvalidArgumentException;

final class RenameZoneUseCase
{
    public function __construct(private ZoneCatalogRepository $zones) {}

    /** @throws AdminChangeRejected */
    public function execute(int $zoneId, string $name): void
    {
        try {
            $zoneName = new ZoneName($name);
        } catch (InvalidArgumentException $invalid) {
            throw new AdminChangeRejected($invalid->getMessage(), previous: $invalid);
        }

        $this->zones->rename($zoneId, $zoneName);
    }
}
