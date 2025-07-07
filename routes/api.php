<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DomainCheckController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/check-domains', [DomainCheckController::class, 'check']);
});
