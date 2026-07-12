<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public Routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

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
    });
});