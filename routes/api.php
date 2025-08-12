<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NodeController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


    // Auth routes
    Route::post('register', [AuthController::class, 'register'])
        ->name('auth.register');
    Route::post('login', [AuthController::class, 'login'])
        ->name('auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('nodes', [NodeController::class, 'store']);
        Route::get('nodes/{node}/children', [NodeController::class, 'getChildren']);
        Route::put('nodes/{node}/change-parent', [NodeController::class, 'updateParent']);
    });

