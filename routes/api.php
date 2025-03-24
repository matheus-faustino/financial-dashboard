<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Transactions
    Route::get('/transactions/by-category/{categoryId}', [TransactionController::class, 'byCategory']);
    Route::get('/transactions/by-tag/{tagId}', [TransactionController::class, 'byTag']);
    Route::get('/transactions/recurring', [TransactionController::class, 'recurring']);
    Route::post('/transactions/{transaction}/tags', [TransactionController::class, 'addTags']);
    Route::delete('/transactions/{transaction}/tags', [TransactionController::class, 'removeTags']);
    Route::apiResource('transactions', TransactionController::class);

    // Categories
    Route::get('/categories/by-type/{type}', [CategoryController::class, 'byType']);
    Route::get('/categories/system', [CategoryController::class, 'system']);
    Route::get('/categories/custom', [CategoryController::class, 'custom']);
    Route::post('/categories/merge', [CategoryController::class, 'merge']);
    Route::apiResource('categories', CategoryController::class);

    // Tags
    Route::get('/tags/by-frequency', [TagController::class, 'byFrequency']);
    Route::get('/tags/search', [TagController::class, 'search']);
    Route::apiResource('tags', TagController::class);

    // Users - only accessible by admins and managers
    Route::middleware('can:manage-users')->group(function () {
        Route::get('/users/by-role/{role}', [UserController::class, 'byRole']);
        Route::get('/users/by-manager/{managerId}', [UserController::class, 'byManager']);
        Route::patch('/users/{user}/status', [UserController::class, 'updateStatus']);
        Route::post('/users/{user}/password', [UserController::class, 'changePassword'])->middleware('can:reset-password,user');
        Route::apiResource('users', UserController::class);
    });
});
