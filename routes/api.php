<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\KosController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\CompareController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ChatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public Routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/midtrans/webhook', [PaymentController::class, 'handleWebhook']);
    
    // Pencari Side / Public Kos Routes (Tanpa Auth)
    Route::get('/kos', [KosController::class, 'search']);
    
    // Route Compare ditaruh di atas route dinamis /kos/{id} untuk menghindari tabrakan route parameter
    Route::get('/kos/compare', [CompareController::class, 'compare']);
    Route::get('/kos/{id}', [KosController::class, 'showPublicDetails']);
    Route::get('/kos/{id}/reviews', [ReviewController::class, 'index']);

    // Protected Routes (Harus menggunakan Sanctum token)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return response()->json([
                'success' => true,
                'message' => 'User profile retrieved successfully',
                'data'    => $request->user()
            ]);
        });
        
        Route::post('/logout', [AuthController::class, 'logout']);

        // Profile Routes (Issue #2)
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto']);

        // Favorite Routes (Issue #5)
        Route::get('/favorites', [FavoriteController::class, 'index']);
        Route::post('/favorites', [FavoriteController::class, 'store']);
        Route::delete('/favorites/{kos_id}', [FavoriteController::class, 'destroy']);

        // Booking Routes (Issue #6)
        Route::get('/bookings', [BookingController::class, 'index']);
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancel']);

        // Review Routes (Issue #8)
        Route::post('/kos/{id}/reviews', [ReviewController::class, 'store']);

        // WhatsApp Chat Tanya Pemilik (Issue #9)
        Route::post('/kos/{id}/tanya', [ChatController::class, 'tanyaPemilik']);

        // Booking Complete khusus untuk Pemilik Kos
        Route::middleware('role:pemilik')->group(function () {
            Route::post('/bookings/{id}/complete', [BookingController::class, 'complete']);
        });

        // Owner Kos Routes (Issue #3) - Hanya bisa diakses oleh role 'pemilik'
        Route::middleware('role:pemilik')->group(function () {
            Route::get('/owner/kos', [KosController::class, 'index']);
            Route::post('/owner/kos', [KosController::class, 'store']);
            Route::put('/owner/kos/{id}', [KosController::class, 'update']);
            Route::delete('/owner/kos/{id}', [KosController::class, 'destroy']);
        });
    });
});