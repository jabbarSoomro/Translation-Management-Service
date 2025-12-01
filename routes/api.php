<?php

use App\Http\Controllers\Api\TranslationController;
use Illuminate\Support\Facades\Route;

Route::prefix('translations')->group(function () {
    Route::get('/', [TranslationController::class, 'index']);
    Route::post('/', [TranslationController::class, 'store']);
    Route::get('/search', [TranslationController::class, 'search']);
    Route::get('/export', [TranslationController::class, 'export']);
    Route::get('/{translation}', [TranslationController::class, 'show']);
    Route::put('/{translation}', [TranslationController::class, 'update']);
    Route::delete('/{translation}', [TranslationController::class, 'destroy']);
});
