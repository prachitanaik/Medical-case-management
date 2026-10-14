<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CaseController;
use Illuminate\Support\Facades\Route;

// All routes here are served under /api (see bootstrap/app.php).

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::name('api.')->middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::apiResource('cases', CaseController::class)
        ->parameters(['cases' => 'case']);
});
