<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [ApiController::class, 'login']);
Route::post('/register', [ApiController::class, 'register']);
Route::get('/village-profile', [ApiController::class, 'villageProfile']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [ApiController::class, 'logout']);
    Route::get('/me', [ApiController::class, 'me']);
    Route::get('/dashboard', [ApiController::class, 'dashboard']);
    Route::get('/letter-types', [ApiController::class, 'letterTypes']);
    Route::get('/letters', [ApiController::class, 'letters']);
    Route::post('/letters', [ApiController::class, 'storeLetter']);
    Route::get('/complaints', [ApiController::class, 'complaints']);
    Route::post('/complaints', [ApiController::class, 'storeComplaint']);
    Route::get('/notifications', [ApiController::class, 'notifications']);
    Route::patch('/notifications/{id}/read', [ApiController::class, 'markNotificationRead']);
});
