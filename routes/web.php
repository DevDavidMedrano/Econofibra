<?php

declare(strict_types=1);

use App\Http\Controllers\CoveragePageController;
use App\Http\Controllers\HomePageController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomePageController::class)->name('home');

Route::get('/cobertura', CoveragePageController::class)->name('coverage.page');
