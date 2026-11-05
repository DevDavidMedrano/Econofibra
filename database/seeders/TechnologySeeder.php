<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Coverage\ValueObjects\TechnologyType;
use App\Infrastructure\Persistence\Eloquent\TechnologyModel;
use Illuminate\Database\Seeder;

/**
 * The real catalog of technologies, which stays when the sample data is removed.
 */
final class TechnologySeeder extends Seeder
{
    public function run(): void
    {
        TechnologyModel::query()->updateOrCreate(['code' => TechnologyType::Fiber->value], ['name' => 'Fibra óptica']);
        TechnologyModel::query()->updateOrCreate(['code' => TechnologyType::Antenna->value], ['name' => 'Internet por antena']);
    }
}
