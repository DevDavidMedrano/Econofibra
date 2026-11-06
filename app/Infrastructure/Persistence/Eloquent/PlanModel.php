<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PlanModel extends Model
{
    protected $table = 'plans';

    protected $fillable = ['technology_id', 'name', 'speed_mbps', 'is_sample'];

    /** @return HasMany<ZonePlanPriceModel, $this> */
    public function prices(): HasMany
    {
        return $this->hasMany(ZonePlanPriceModel::class, 'plan_id');
    }

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
