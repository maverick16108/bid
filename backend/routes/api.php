<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);

    // Admin / Moderator Routes
    Route::prefix('admin')->group(function () {
        Route::get('/me', [\App\Http\Controllers\Api\AdminController::class, 'me']);
        Route::get('/stats', [\App\Http\Controllers\Api\AdminController::class, 'stats']);
        
        // Moderators (Master Only)
        Route::get('/moderators', [\App\Http\Controllers\Api\AdminController::class, 'indexModerators']);
        Route::post('/moderators', [\App\Http\Controllers\Api\AdminController::class, 'storeModerator']);
        Route::get('/moderators/{id}', [\App\Http\Controllers\Api\AdminController::class, 'showModerator']);
        Route::put('/moderators/{id}', [\App\Http\Controllers\Api\AdminController::class, 'updateModerator']);
        Route::delete('/moderators/{id}', [\App\Http\Controllers\Api\AdminController::class, 'deleteModerator']);
        
        // Users
        Route::get('/users', [\App\Http\Controllers\Api\AdminController::class, 'users']);
        Route::post('/users', [\App\Http\Controllers\Api\AdminController::class, 'storeUser']);
        Route::get('/users/{id}', [\App\Http\Controllers\Api\AdminController::class, 'showUser']);
        Route::put('/users/{id}', [\App\Http\Controllers\Api\AdminController::class, 'updateUser']);
        Route::delete('/users/{id}', [\App\Http\Controllers\Api\AdminController::class, 'deleteUser']);

        // Bids
        Route::get('/bids', [\App\Http\Controllers\Api\AdminController::class, 'bids']);
        Route::delete('/bids/{id}', [\App\Http\Controllers\Api\AdminController::class, 'deleteBid']);
    });
});

Route::prefix('admin')->group(function () {
    Route::post('/login', [\App\Http\Controllers\Api\AdminController::class, 'login']);
});