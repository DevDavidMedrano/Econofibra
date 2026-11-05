<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

final class CoverageZoneModel extends Model
{
    protected $table = 'coverage_zones';

    protected $fillable = ['name', 'boundary', 'is_sample'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'boundary' => 'array',
            'is_sample' => 'boolean',
        ];
    }
}
