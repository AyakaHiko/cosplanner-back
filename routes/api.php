<?php

use App\Http\Controllers\Api\ApiAuthenticatedController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:api')->group(function () {
    Route::get('/user', [ApiAuthenticatedController::class, 'user']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::delete('/profile', [ProfileController::class, 'destroy']);
    Route::post('/update-avatar', [ProfileController::class, 'updateAvatar']);
});

Route::middleware('guest')->group(function () {
    Route::controller(ApiAuthenticatedController::class)->group(function () {
        Route::post('/register', 'register');
        Route::post('/login', 'login');
    });
});
Route::post('/image-store', [ImageController::class, 'store']);


