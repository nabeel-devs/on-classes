<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\user\PostController;
use App\Http\Controllers\api\user\ContactController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::group(['prefix' => 'user'], function () {

    Route::post('/register', [App\Http\Controllers\api\user\AuthController::class, 'register']);
    Route::post('/login', [App\Http\Controllers\api\user\AuthController::class, 'login']);
    Route::post('/forgot-password', [App\Http\Controllers\api\user\PasswordResetController::class, 'forgotPassword']);
    Route::post('/reset-password', [App\Http\Controllers\api\user\PasswordResetController::class, 'resetPassword']);

    Route::get('/auth/google/redirect', [App\Http\Controllers\api\user\SocialAuthController::class, 'redirectToGoogle']);
    Route::get('/auth/google/callback', [App\Http\Controllers\api\user\SocialAuthController::class, 'handleGoogleCallback']);

    Route::get('/auth/facebook/redirect', [App\Http\Controllers\api\user\SocialAuthController::class, 'redirectToFacebook']);
    Route::get('/auth/facebook/callback', [App\Http\Controllers\api\user\SocialAuthController::class, 'handleFacebookCallback']);

    Route::prefix('contacts')->controller(ContactController::class)->group(function () {
        Route::get('/', 'index')->name('contacts.index');
        Route::post('/', 'store')->name('contacts.store');
        Route::get('/{contact}', 'show')->name('contacts.show');
    });

    Route::middleware(['auth:sanctum'])->group(function () {

        Route::post('/email/verify', [App\Http\Controllers\api\user\EmailVerificationController::class, 'verify']);
        Route::post('/email/resend', [App\Http\Controllers\api\user\EmailVerificationController::class, 'resend']);

        Route::middleware(['verified'])->group(function () {
            Route::post('/subscribe', [App\Http\Controllers\api\user\SubscriptionController::class, 'subscribe']);
            Route::post('/subscription/payment-method', [App\Http\Controllers\api\user\SubscriptionController::class, 'updatePaymentMethod']);
            Route::get('/subscription/status', [App\Http\Controllers\api\user\SubscriptionController::class, 'status']);




            Route::prefix('posts')->middleware('auth')->controller(PostController::class)->group(function () {
                Route::get('/', 'index')->name('posts.index');
                Route::post('/', 'store')->name('posts.store');
                Route::get('/{post}', 'show')->name('posts.show');
                Route::put('/{post}', 'update')->name('posts.update');
                Route::delete('/{post}', 'destroy')->name('posts.destroy');
            });
        });
    });
});







// Route::group(['prefix' => 'member'], function () {

//     Route::post('/register', [App\Http\Controllers\api\member\AuthController::class, 'register']);
//     Route::post('/login', [App\Http\Controllers\api\member\AuthController::class, 'login']);
//     Route::post('/forgot-password', [App\Http\Controllers\api\member\PasswordResetController::class, 'forgotPassword']);
//     Route::post('/reset-password', [App\Http\Controllers\api\member\PasswordResetController::class, 'resetPassword']);

//     Route::middleware(['auth:user'])->group(function () {

//         Route::post('/email/verify', [App\Http\Controllers\api\member\EmailVerificationController::class, 'verify']);
//         Route::post('/email/resend', [App\Http\Controllers\api\member\EmailVerificationController::class, 'resend']);

//         Route::middleware(['verified'])->group(function () {

//         });
//     });
// });

