<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\KosController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public Routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // Pencari Side / Public Kos Routes (Tanpa Auth)
    Route::get('/kos', [KosController::class, 'search']);
    Route::get('/kos/{id}', [KosController::class, 'showPublicDetails']);

    // Protected Routes (Harus menggunakan Sanctum token)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return response()->json([
                'success' => true,
                'message' => 'User profile retrieved successfully',
                'data' => $request->user()
            ]);
        });
        
        Route::post('/logout', [AuthController::class, 'logout']);

        // Profile Routes (Issue #2)
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto']);

        // Owner Kos Routes (Issue #3) - Hanya bisa diakses oleh role 'pemilik'
        Route::middleware('role:pemilik')->group(function () {
            Route::get('/owner/kos', [KosController::class, 'index']);
            Route::post('/owner/kos', [KosController::class, 'store']);
            Route::put('/owner/kos/{id}', [KosController::class, 'update']);
            Route::delete('/owner/kos/{id}', [KosController::class, 'destroy']);
        });
    });
});