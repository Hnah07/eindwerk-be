<?php

use App\Http\Controllers\ConcertController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ArtistController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('concerts', ConcertController::class);
    Route::apiResource('locations', LocationController::class);
    Route::apiResource('artists', ArtistController::class);
});
