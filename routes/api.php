<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

// Public routes with rate limiting
Route::middleware(['throttle:5,1'])->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
});

// Protected routes
Route::middleware('auth:api')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::get('user', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    
    // User CRUD routes
    Route::apiResource('users', UserController::class);
    Route::get('users/stats/overview', [UserController::class, 'stats']);
});

// Fallback for undefined routes
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Endpoint not found'
    ], 404);
});

// Handle method not allowed for login and register
Route::match(['GET', 'PUT', 'PATCH', 'DELETE'], 'login', function () {
    return response()->json([
        'success' => false,
        'message' => 'Method not allowed. Use POST method for login.'
    ], 405);
});

Route::match(['GET', 'PUT', 'PATCH', 'DELETE'], 'register', function () {
    return response()->json([
        'success' => false,
        'message' => 'Method not allowed. Use POST method for register.'
    ], 405);
});
