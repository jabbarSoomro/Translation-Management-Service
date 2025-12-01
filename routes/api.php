<?php


use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TranslationController;
use Illuminate\Support\Facades\Route;

//  auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Translation routes
    Route::prefix('translations')->group(function () {
        Route::get('/', [TranslationController::class, 'index']);
        Route::post('/', [TranslationController::class, 'store']);
        Route::get('/search', [TranslationController::class, 'search']);
        Route::get('/export', [TranslationController::class, 'export']);
        Route::get('/{translation}', [TranslationController::class, 'show']);
        Route::put('/{translation}', [TranslationController::class, 'update']);
        Route::delete('/{translation}', [TranslationController::class, 'destroy']);
    });
});
