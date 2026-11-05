<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Plans\ValueObjects\PlanCardIcon;
use App\Infrastructure\Persistence\Eloquent\PlanCardModel;
use Illuminate\Database\Seeder;

/**
 * The real plan cards, which stay when the sample data is removed.
 * With any card stored it does nothing, so edited cards are never overwritten.
 */
final class PlanCardSeeder extends Seeder
{
    public function run(): void
    {
        if (PlanCardModel::query()->exists()) {
            return;
        }

        $cards = [
            ['Hogar Conectado', 50, 'Navega, estudia y disfruta', PlanCardIcon::Casa, false],
            ['Gaming', 100, 'Juega sin lag', PlanCardIcon::Control, false],
            ['Negocios', 200, 'Trabaja sin interrupciones', PlanCardIcon::Maletin, false],
            ['Premium', 300, 'Máximo rendimiento', PlanCardIcon::Cohete, true],
        ];

        foreach ($cards as $index => [$name, $speedMbps, $phrase, $icon, $isPopular]) {
            PlanCardModel::query()->create([
                'name' => $name,
                'speed_mbps' => $speedMbps,
                'phrase' => $phrase,
                'icon_key' => $icon->value,
                'position' => $index + 1,
                'is_popular' => $isPopular,
            ]);
        }
    }
}
