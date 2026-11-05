<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

final class PlanCardModel extends Model
{
    protected $table = 'plan_cards';

    protected $fillable = ['name', 'speed_mbps', 'phrase', 'icon_key', 'position', 'is_popular'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'speed_mbps' => 'integer',
            'position' => 'integer',
            'is_popular' => 'boolean',
        ];
    }
}
