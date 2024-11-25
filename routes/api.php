<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\user\ChatController;
use App\Http\Controllers\api\user\PostController;
use App\Http\Controllers\api\user\FollowController;
use App\Http\Controllers\api\user\ContactController;
use App\Http\Controllers\api\user\MessageController;
use App\Http\Controllers\api\user\ProfileController;
use App\Http\Controllers\api\user\PostLikeController;
use App\Http\Controllers\api\user\UserLinkController;
use App\Http\Controllers\api\user\PostCommentController;
use App\Http\Controllers\api\user\PostBookmarkController;

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

    Route::get('/auth/google', [App\Http\Controllers\api\user\SocialAuthController::class, 'googleLogin']);
    Route::post('/auth/google/store', [App\Http\Controllers\api\user\SocialAuthController::class, 'googleStore']);

    Route::get('/auth/facebook/redirect', [App\Http\Controllers\api\user\SocialAuthController::class, 'redirectToFacebook']);
    Route::get('/auth/facebook/callback', [App\Http\Controllers\api\user\SocialAuthController::class, 'handleFacebookCallback']);

    Route::prefix('contacts')->controller(ContactController::class)->group(function () {
        Route::get('/', 'index')->name('contacts.index');
        Route::post('/', 'store')->name('contacts.store');
        Route::get('/{contact}', 'show')->name('contacts.show');
    });

    Route::middleware(['auth'])->group(function () {

        Route::post('/email/verify', [App\Http\Controllers\api\user\EmailVerificationController::class, 'verify']);
        Route::post('/email/resend', [App\Http\Controllers\api\user\EmailVerificationController::class, 'resend']);


        Route::middleware(['verified'])->group(function () {
            Route::post('/create-password', [App\Http\Controllers\api\user\AuthController::class, 'createPassword']);


            Route::post('/subscribe', [App\Http\Controllers\api\user\SubscriptionController::class, 'subscribe']);
            Route::post('/subscription/payment-method', [App\Http\Controllers\api\user\SubscriptionController::class, 'updatePaymentMethod']);
            Route::get('/subscription/status', [App\Http\Controllers\api\user\SubscriptionController::class, 'status']);
            Route::get('/subscription/plans', [App\Http\Controllers\api\user\SubscriptionController::class, 'getPlans']);




            Route::prefix('profile')->group(function () {

                Route::get('/', [ProfileController::class, 'show']);

                Route::post('/dp', [ProfileController::class, 'uploadDp']);
                Route::get('/dp', [ProfileController::class, 'getDp']);
                Route::put('/update', [ProfileController::class, 'update']);

            });

            Route::prefix('links')->group(function () {

                Route::post('/', [UserLinkController::class, 'store']);
                Route::get('/', [UserLinkController::class, 'index']);
                Route::get('/{userLink}', [UserLinkController::class, 'show']);
                Route::put('/{userLink}', [UserLinkController::class, 'update']);
                Route::delete('/{userLink}', [UserLinkController::class, 'destroy']);

            });


            Route::prefix('posts')->controller(PostController::class)->group(function () {
                Route::get('/', 'index');
                Route::post('/', 'store');
                Route::get('/{post}', 'show');
                Route::put('/{post}', 'update');
                Route::delete('/{post}', 'destroy');
                Route::get('/{user}/posts', 'userPosts');

            });
            Route::prefix('posts')->group(function () {
                Route::post('{post}/comments', [PostCommentController::class, 'store']);
                Route::delete('comments/{comment}', [PostCommentController::class, 'destroy']);

                // Post Likes
                Route::post('{post}/likes', [PostLikeController::class, 'store']);
                Route::delete('likes/{like}', [PostLikeController::class, 'destroy']);

                // Post Bookmarks
                Route::post('{post}/bookmarks', [PostBookmarkController::class, 'store']);
                Route::delete('bookmarks/{bookmark}', [PostBookmarkController::class, 'destroy']);
            });


            Route::prefix('chats')->group(function () {

                Route::get('/', [ChatController::class, 'index']);
                Route::post('/', [ChatController::class, 'store']);
                Route::get('/{chat}', [ChatController::class, 'show']);

                // Message-related routes
                Route::post('/{chat}/messages', [MessageController::class, 'store']);
                Route::get('/{chat}/messages', [MessageController::class, 'index']);
                Route::patch('/messages/{message}/read', [MessageController::class, 'markAsRead']);

            });


            Route::post('/follow/{user}', [FollowController::class, 'follow']);
            Route::delete('/unfollow/{user}', [FollowController::class, 'unfollow']);
            Route::get('/followers', [FollowController::class, 'getFollowers']);
            Route::get('/followings', [FollowController::class, 'getFollowings']);
            Route::get('/user/{user?}/follow/count', [FollowController::class, 'getFollowCounts']);










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

