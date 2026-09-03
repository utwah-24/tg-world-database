<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\LogoController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\SoldCarController;
use App\Http\Controllers\Api\TestDriveController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->middleware('frontend.origin')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth-register');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth-login');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:auth-recovery');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:auth-recovery');
    Route::middleware('customer.auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::prefix('favorites')->middleware(['frontend.origin', 'customer.auth'])->group(function () {
    Route::get('/', [FavoriteController::class, 'index'])->middleware('throttle:favorites-list');
    Route::post('/', [FavoriteController::class, 'store'])->middleware('throttle:favorites-mutate');
    Route::delete('/{carId}', [FavoriteController::class, 'destroy'])->middleware('throttle:favorites-mutate');
    Route::post('/{carId}/remove', [FavoriteController::class, 'destroy'])->middleware('throttle:favorites-mutate');
});

Route::get('/cars', [CarController::class, 'index']);
Route::get('/cars/{carId}', [CarController::class, 'show'])->whereNumber('carId');
Route::get('/categories', [CarController::class, 'categories']);
Route::get('/third-party', [CarController::class, 'thirdParty']);
Route::get('/content', [ContentController::class, 'index']);
Route::get('/logos', [LogoController::class, 'index']);
Route::get('/promotions', [PromotionController::class, 'index']);
Route::get('/companies', [CompanyController::class, 'index']);
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders', [OrderController::class, 'index']);
Route::get('/orders/{id}', [OrderController::class, 'show'])->whereNumber('id');
Route::get('/sold-cars', [SoldCarController::class, 'index']);
Route::get('/sold-cars/{id}', [SoldCarController::class, 'show'])->whereNumber('id');
Route::post('/test-drives', [TestDriveController::class, 'store']);
