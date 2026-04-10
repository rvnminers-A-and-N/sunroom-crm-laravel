<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DealController;
use App\Http\Controllers\Api\TagController;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index']);

    // Contacts
    Route::apiResource('contacts', ContactController::class);
    Route::post('contacts/{id}/tags', [ContactController::class, 'syncTags']);

    // Companies
    Route::apiResource('companies', CompanyController::class);

    // Deals
    Route::get('deals/pipeline', [DealController::class, 'pipeline']);
    Route::apiResource('deals', DealController::class);

    // Activities
    Route::apiResource('activities', ActivityController::class);

    // Tags
    Route::get('tags', [TagController::class, 'index']);
    Route::post('tags', [TagController::class, 'store']);
    Route::put('tags/{id}', [TagController::class, 'update']);
    Route::delete('tags/{id}', [TagController::class, 'destroy']);
});
