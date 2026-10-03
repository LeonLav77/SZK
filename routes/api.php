<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\CouponController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('edition', [ContentController::class, 'edition']);
    Route::get('settings', [ContentController::class, 'settings']);
    Route::get('categories', [ContentController::class, 'categories']);
    Route::get('print-issues', [ContentController::class, 'printIssues']);

    Route::get('coupons', [CouponController::class, 'index']);
    Route::get('coupons/{id}', [CouponController::class, 'show'])->whereNumber('id');
    Route::post('coupons/{id}/redeem', [CouponController::class, 'redeem'])
        ->whereNumber('id')
        ->middleware('throttle:30,1');

    Route::middleware('throttle:10,1')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::delete('me', [AuthController::class, 'destroy']);
    });
});
