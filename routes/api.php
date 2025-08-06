<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;

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
    
    // User CRUD routes - accessible to users with view users permission
    Route::middleware('permission:view users')->group(function () {
        Route::get('users/stats/overview', [UserController::class, 'stats']);
        Route::get('users', [UserController::class, 'index']);
        Route::get('users/{user}', [UserController::class, 'show']);
    });
    
    // User management routes - based on permissions
    Route::post('users', [UserController::class, 'store'])->middleware('permission:create users');
    Route::put('users/{user}', [UserController::class, 'update'])->middleware('permission:edit users');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware('permission:delete users');
    
    // Permission statistics - accessible to users with permission management access
    Route::get('permissions/stats/overview', [PermissionController::class, 'stats'])->middleware('permission:manage permissions|view permissions');
    
    // Role and Permission management routes
    Route::middleware('permission:manage roles')->group(function () {
        Route::apiResource('roles', RoleController::class);
        Route::post('roles/{role}/assign-user', [RoleController::class, 'assignToUser']);
        Route::post('roles/{role}/remove-user', [RoleController::class, 'removeFromUser']);
    });
    
    Route::middleware('permission:manage permissions')->group(function () {
        Route::apiResource('permissions', PermissionController::class);
        Route::get('permissions/grouped', [PermissionController::class, 'grouped']);
        Route::post('permissions/{permission}/assign-user', [PermissionController::class, 'assignToUser']);
        Route::post('permissions/{permission}/remove-user', [PermissionController::class, 'removeFromUser']);
    });
    
    // Allow viewing roles and permissions for users with appropriate permissions
    Route::get('roles', [RoleController::class, 'index'])->middleware('permission:manage roles|view roles');
    Route::get('permissions', [PermissionController::class, 'index'])->middleware('permission:manage permissions|view permissions');
    Route::get('permissions/all', [PermissionController::class, 'all'])->middleware('permission:manage roles|view roles|manage permissions|view permissions');
    Route::get('roles/{role}', [RoleController::class, 'show'])->middleware('permission:manage roles|view roles');
    Route::get('permissions/{permission}', [PermissionController::class, 'show'])->middleware('permission:manage permissions|view permissions')->where('permission', '[0-9]+');
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
