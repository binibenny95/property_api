<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NodeController;
use App\Http\Controllers\AuthController;

// Auth routes (no auth required)
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Protected routes (auth required)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);

    // Node routes
    Route::post('nodes', [NodeController::class, 'store']);
    Route::get('nodes/{node}/children', [NodeController::class, 'getChildren']);
    Route::put('nodes/{node}/change-parent', [NodeController::class, 'updateParent']);
});
