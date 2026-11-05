<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ZonePlanPriceModel extends Model
{
    protected $table = 'zone_plan_prices';

    protected $fillable = ['zone_id', 'plan_id', 'price_cents', 'installation_cost_cents', 'is_sample'];

    /** @return BelongsTo<PlanModel, $this> */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanModel::class, 'plan_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
            'installation_cost_cents' => 'integer',
            'is_sample' => 'boolean',
        ];
    }
}
