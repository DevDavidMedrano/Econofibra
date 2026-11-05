<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

final class PlanModel extends Model
{
    protected $table = 'plans';

    protected $fillable = ['technology_id', 'name', 'speed_mbps', 'is_sample'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'technology_id' => 'integer',
            'speed_mbps' => 'integer',
            'is_sample' => 'boolean',
        ];
    }
}
