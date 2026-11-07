<?php

declare(strict_types=1);

use App\Http\Controllers\CoverageCheckController;
use Illuminate\Support\Facades\Route;

if (config('site.static')) {
    return;
}

Route::post('/coverage/check', CoverageCheckController::class)->name('coverage.check');
