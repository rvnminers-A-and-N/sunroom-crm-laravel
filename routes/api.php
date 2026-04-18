<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DealController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\UserController;
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

    // AI
    Route::prefix('ai')->group(function () {
        Route::post('summarize', [AiController::class, 'summarize']);
        Route::post('summarize/stream', [AiController::class, 'summarizeStream']);
        Route::post('ask/stream', [AiController::class, 'askStream']);
        Route::post('deal-insights/{dealId}', [AiController::class, 'dealInsights']);
        Route::post('deal-insights/{dealId}/stream', [AiController::class, 'dealInsightsStream']);
        Route::post('search', [AiController::class, 'search']);
        Route::post('search/stream', [AiController::class, 'searchStream']);
    });

    // Users (admin only)
    Route::middleware('role:Admin')->group(function () {
        Route::get('users', [UserController::class, 'index']);
        Route::get('users/{id}', [UserController::class, 'show']);
        Route::post('users', [UserController::class, 'store']);
        Route::put('users/{id}', [UserController::class, 'update']);
        Route::delete('users/{id}', [UserController::class, 'destroy']);
    });
});
