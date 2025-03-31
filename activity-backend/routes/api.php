<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Laravel\Passport\Http\Controllers\AccessTokenController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::post('/oauth/token', [AccessTokenController::class, 'issueToken'])
    ->middleware(['throttle:60,1'])
    ->name('passport.token');

Route::group(["prefix" => "v1"], function () {
    Route::group(["prefix" => "guest"], function () {
        Route::post("/register", [AuthController::class, "register"])->name('register');
        Route::post("/login", [AuthController::class, "login"])->name('login');
    });
});
