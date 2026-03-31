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
    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::delete('/profile', [ProfileController::class, 'destroy']);
    Route::post('/update-avatar', [ProfileController::class, 'updateAvatar']);

    // Cosplan CRUD
    Route::apiResource('cosplans', \App\Http\Controllers\CosplanController::class);
    Route::apiResource('cosplans.images', \App\Http\Controllers\CosplanImageController::class)->only(['index', 'store', 'destroy']);
    Route::apiResource('cosplans.materials', \App\Http\Controllers\CosplanMaterialController::class)->only(['index', 'store', 'destroy']);
    Route::post('cosplans/{cosplan}/albums/create-and-upload', [\App\Http\Controllers\CosplanAlbumController::class, 'createAndUpload']);
    Route::apiResource('cosplans.albums', \App\Http\Controllers\CosplanAlbumController::class)->only(['store', 'destroy']);
});

Route::middleware('guest')->group(function () {
    Route::controller(ApiAuthenticatedController::class)->group(function () {
        Route::post('/register', 'register');
        Route::post('/login', 'login');
    });
});


