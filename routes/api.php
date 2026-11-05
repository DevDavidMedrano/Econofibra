<?php

declare(strict_types=1);

use App\Http\Controllers\CoverageCheckController;
use Illuminate\Support\Facades\Route;

Route::post('/coverage/check', CoverageCheckController::class)->name('coverage.check');
