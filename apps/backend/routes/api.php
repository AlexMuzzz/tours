<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\TourController as AdminTourController;
use App\Http\Controllers\Admin\TourDateController;
use App\Http\Controllers\Admin\TourImageController;
use App\Http\Controllers\Admin\TourRoutePointController;
use App\Http\Controllers\PublicApi\SemanticSearchController;
use App\Http\Controllers\PublicApi\TourController;

Route::get('/tours/search/semantic', SemanticSearchController::class);
Route::get('/tours', [TourController::class, 'index']);
Route::get('/tours/{slug}', [TourController::class, 'show']);

Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        Route::get('/tours', [AdminTourController::class, 'index']);
        Route::post('/tours', [AdminTourController::class, 'store']);
        Route::get('/tours/{tour}', [AdminTourController::class, 'show']);
        Route::put('/tours/{tour}', [AdminTourController::class, 'update']);
        Route::delete('/tours/{tour}', [AdminTourController::class, 'destroy']);

        Route::post('/tours/{tour}/images', [TourImageController::class, 'store']);
        Route::delete('/tour-images/{tourImage}', [TourImageController::class, 'destroy']);

        Route::post('/tours/{tour}/dates', [TourDateController::class, 'store']);
        Route::put('/tour-dates/{tourDate}', [TourDateController::class, 'update']);
        Route::delete('/tour-dates/{tourDate}', [TourDateController::class, 'destroy']);

        Route::post('/tours/{tour}/route-points', [TourRoutePointController::class, 'store']);
        Route::put('/tour-route-points/{tourRoutePoint}', [TourRoutePointController::class, 'update']);
        Route::delete('/tour-route-points/{tourRoutePoint}', [TourRoutePointController::class, 'destroy']);
    });
});
