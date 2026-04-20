<?php

use App\Http\Controllers\Api\ApiAuthenticatedController;
use App\Http\Controllers\CosplanAlbumController;
use App\Http\Controllers\CosplanController;
use App\Http\Controllers\CosplanImageController;
use App\Http\Controllers\CosplanMaterialController;
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

    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'show');
        Route::patch('/profile', 'update');
        Route::delete('/profile', 'destroy');
        Route::post('/update-avatar', 'updateAvatar');
    });
    Route::post('/email/verification-notification', [ApiAuthenticatedController::class, 'verificationNotification'])
        ->middleware('throttle:3,1')
        ->name('verification.send');

    // Cosplan CRUD
    Route::middleware('verified')->group(function () {
        Route::apiResource('cosplans', CosplanController::class);
        Route::apiResource('cosplans.images', CosplanImageController::class)->only(['index', 'store', 'destroy']);
        Route::apiResource('cosplans.materials', CosplanMaterialController::class)->only(['index', 'store', 'destroy']);
        Route::post('cosplans/{cosplan}/albums/create-and-upload', [CosplanAlbumController::class, 'createAndUpload']);
        Route::apiResource('cosplans.albums', CosplanAlbumController::class)->only(['store', 'destroy']);
    });

});

Route::middleware('guest')->group(function () {
    Route::controller(ApiAuthenticatedController::class)->group(function () {
        Route::post('/register', 'register');
        Route::post('/login', 'login');
        Route::post('/forgot-password', 'sendResetLinkEmail')->name('password.email');
        Route::post('/reset-password', 'resetPassword')->name('password.update');
    });
});
Route::get('/email/verify/{id}/{hash}', [ApiAuthenticatedController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

