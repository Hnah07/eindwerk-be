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

// create api endpoint for concerts using ConcertController
// Route::get('/concerts', [App\Http\Controllers\ConcertController::class, 'index']);
// Route::get('/concerts/{id}', [App\Http\Controllers\ConcertController::class, 'show']);
// Route::post('/concerts', [App\Http\Controllers\ConcertController::class, 'store']);
// Route::put('/concerts/{id}', [App\Http\Controllers\ConcertController::class, 'update']);
// Route::delete('/concerts/{id}', [App\Http\Controllers\ConcertController::class, 'destroy']);

// -> kortere routes

Route::apiResource('concerts', ConcertController::class)->only(['index', 'store', 'show', 'destroy', 'update']);
//Route::apiResource('users', UserController::class)->only(['index', 'store', 'show', 'destroy', 'update']);
Route::apiResource('locations', LocationController::class)->only(['index', 'store', 'show', 'destroy', 'update']);
Route::apiResource('artists', ArtistController::class)->only(['index', 'store', 'show', 'destroy', 'update']);
