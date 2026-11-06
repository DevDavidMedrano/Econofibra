<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SampleDataController;
use App\Http\Controllers\Admin\ZoneController;
use App\Http\Controllers\Admin\ZoneImportController;
use App\Http\Controllers\Admin\ZonePriceController;
use App\Http\Controllers\Admin\ZoneTechnologyController;
use App\Http\Controllers\CoveragePageController;
use App\Http\Controllers\HomePageController;
use App\Http\Middleware\EndIdleAdminSession;
use Illuminate\Support\Facades\Route;

Route::get('/', HomePageController::class)->name('home');

Route::get('/cobertura', CoveragePageController::class)->name('coverage.page');

Route::prefix('panel')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/entrar', [LoginController::class, 'show'])->name('login');
        Route::post('/entrar', [LoginController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'auth.session', EndIdleAdminSession::class])->group(function (): void {
        Route::get('/', [SampleDataController::class, 'index'])->name('dashboard');
        Route::delete('/datos-de-ejemplo', [SampleDataController::class, 'destroy'])->name('sample.destroy');
        Route::post('/salir', [LoginController::class, 'destroy'])->name('logout');

        Route::prefix('zonas')->name('zones.')->where(['zone' => '[0-9]{1,18}', 'plan' => '[0-9]{1,18}'])->group(function (): void {
            Route::get('/', [ZoneController::class, 'index'])->name('index');
            Route::post('/importar', ZoneImportController::class)->name('import');
            Route::patch('/{zone}', [ZoneController::class, 'update'])->name('update');
            Route::delete('/{zone}', [ZoneController::class, 'destroy'])->name('destroy');
            Route::post('/{zone}/tecnologias', ZoneTechnologyController::class)->name('technology');
            Route::get('/{zone}/precios', [ZonePriceController::class, 'index'])->name('prices.index');
            Route::post('/{zone}/precios', [ZonePriceController::class, 'store'])->name('prices.store');
            Route::delete('/{zone}/precios/{plan}', [ZonePriceController::class, 'destroy'])->name('prices.destroy');
        });

        Route::prefix('planes')->name('plans.')->where(['plan' => '[0-9]{1,18}'])->group(function (): void {
            Route::get('/', [PlanController::class, 'index'])->name('index');
            Route::post('/', [PlanController::class, 'store'])->name('store');
            Route::patch('/{plan}', [PlanController::class, 'update'])->name('update');
            Route::delete('/{plan}', [PlanController::class, 'destroy'])->name('destroy');
        });
    });
});
