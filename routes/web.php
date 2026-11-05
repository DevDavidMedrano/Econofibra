<?php

declare(strict_types=1);

use App\Http\Controllers\CoveragePageController;
use Illuminate\Support\Facades\Route;

// Mientras no exista la página principal, la raíz lleva a cobertura.
Route::redirect('/', '/cobertura');

Route::get('/cobertura', CoveragePageController::class)->name('coverage.page');
