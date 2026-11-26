<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\EditionController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TitularController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

// Public site: one page, hash-routed in public/app.js, all data from /api/v1.
Route::view('/', 'app');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'create'])->name('login');
    Route::post('login', [AuthController::class, 'store'])->middleware('throttle:10,1');
    Route::post('logout', [AuthController::class, 'destroy'])->name('logout');

    Route::middleware(['auth', 'admin'])->group(function () {
        // The editions list is the admin home page.
        Route::redirect('/', '/admin/editions')->name('dashboard');

        Route::resource('editions', EditionController::class);
        Route::post('editions/{edition}/activate', [EditionController::class, 'activate'])->name('editions.activate');
        Route::resource('editions.coupons', CouponController::class)->shallow()->except(['index', 'show']);

        Route::resource('titulars', TitularController::class)->except('show');
        Route::post('titulars/{titular}/locations', [LocationController::class, 'store'])->name('locations.store');
        Route::delete('locations/{location}', [LocationController::class, 'destroy'])->name('locations.destroy');

        Route::resource('users', UserController::class)->only(['index', 'edit', 'update', 'destroy']);

        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
