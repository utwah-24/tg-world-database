<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\LogoController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/cars', [CarController::class, 'index']);
Route::get('/cars/{carId}', [CarController::class, 'show'])->whereNumber('carId');
Route::get('/categories', [CarController::class, 'categories']);
Route::get('/third-party', [CarController::class, 'thirdParty']);
Route::get('/content', [ContentController::class, 'index']);
Route::get('/logos', [LogoController::class, 'index']);
Route::get('/companies', [CompanyController::class, 'index']);
