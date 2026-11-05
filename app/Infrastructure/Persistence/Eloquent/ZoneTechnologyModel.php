<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

final class ZoneTechnologyModel extends Model
{
    protected $table = 'zone_technologies';

    protected $fillable = ['zone_id', 'technology_id', 'status', 'is_sample'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_sample' => 'boolean'];
    }
}
