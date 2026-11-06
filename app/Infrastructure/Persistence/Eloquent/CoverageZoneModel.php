<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Coverage\ValueObjects\ZoneName;
use Illuminate\Database\Eloquent\Model;

final class CoverageZoneModel extends Model
{
    protected $table = 'coverage_zones';

    protected $fillable = ['name', 'boundary', 'boundary_replaced', 'is_sample'];

    protected static function booted(): void
    {
        self::saving(static function (self $zone): void {
            $zone->name_key = (new ZoneName($zone->name))->key();
        });
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'boundary' => 'array',
            'boundary_replaced' => 'boolean',
            'is_sample' => 'boolean',
        ];
    }
}
