<?php

use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Cosplan\CosplanAlbumController;
use App\Http\Controllers\Cosplan\CosplanController;
use App\Http\Controllers\Cosplan\CosplanImageController;
use App\Http\Controllers\Cosplan\CosplanMaterialController;
use App\Http\Controllers\User\ProfileController;
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

Route::middleware(['auth:api', 'verified'])->group(function () {

    Route::middleware('can:admin')->prefix('admin')->group(function () {
       Route::apiResource('users',UserManagementController::class);
    });

    Route::get('/user', [AuthController::class, 'user']);

    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'show');
        Route::patch('/profile', 'update');
        Route::delete('/profile', 'destroy');
        Route::post('/update-avatar', 'updateAvatar');
    });
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'verificationNotification'])
        ->middleware('throttle:3,1')
        ->name('verification.send');

    // Cosplan CRUD
    Route::apiResource('cosplans', CosplanController::class);
    Route::apiResource('cosplans.images', CosplanImageController::class)->only(['index', 'store', 'destroy']);
    Route::apiResource('cosplans.materials', CosplanMaterialController::class)->only(['index', 'store', 'destroy']);
    Route::post('cosplans/{cosplan}/albums/create-and-upload', [CosplanAlbumController::class, 'createAndUpload']);
    Route::apiResource('cosplans.albums', CosplanAlbumController::class)->only(['store', 'destroy']);

});

Route::middleware('guest')->group(function () {
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');

    Route::post('/auth/google/callback', [AuthController::class, 'googleCallback']);
});
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

