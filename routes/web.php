<?php

use App\Http\Controllers\CarViewController;
use App\Http\Controllers\SwaggerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/swagger', [SwaggerController::class, 'ui']);
Route::get('/docs/openapi.json', [SwaggerController::class, 'spec']);
Route::get('/cars/{carId}', [CarViewController::class, 'show'])->whereNumber('carId');
