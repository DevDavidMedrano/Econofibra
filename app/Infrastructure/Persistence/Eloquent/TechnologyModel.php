<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

final class TechnologyModel extends Model
{
    protected $table = 'technologies';

    protected $fillable = ['code', 'name'];
}
