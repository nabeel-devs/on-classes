<?php

use App\Http\Controllers\api\CategoryController;
use App\Http\Controllers\api\creator\CourseController;
use App\Http\Controllers\api\creator\InsightController;
use App\Http\Controllers\api\creator\LessonController;
use App\Http\Controllers\api\creator\ModuleController;
use App\Http\Controllers\api\creator\ProductController;
use App\Http\Controllers\api\creator\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\user\ChatController;
use App\Http\Controllers\api\user\PostController;
use App\Http\Controllers\api\user\FollowController;
use App\Http\Controllers\api\user\ContactController;
use App\Http\Controllers\api\user\DiplomaController;
use App\Http\Controllers\api\user\MessageController;
use App\Http\Controllers\api\user\NotificationController;
use App\Http\Controllers\api\user\ProfileController;
use App\Http\Controllers\api\user\PostLikeController;
use App\Http\Controllers\api\user\UserLinkController;
use App\Http\Controllers\api\user\PostCommentController;
use App\Http\Controllers\api\user\PostBookmarkController;
use App\Http\Controllers\api\user\ProductOrderController;

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
                Route::put('/update/role', [ProfileController::class, 'updateRole']);


            });

            Route::prefix('diplomas')->group(function () {

                Route::get('/', [DiplomaController::class, 'index']);

                Route::post('/upload', [DiplomaController::class, 'uploadDiplomaData']);

                Route::get('/{diploma}', [DiplomaController::class, 'previewDiploma']);

            });

            Route::prefix('links')->group(function () {

                Route::post('/', [UserLinkController::class, 'store']);
                Route::get('/', [UserLinkController::class, 'index']);
                Route::get('/{userLink}', [UserLinkController::class, 'show']);
                Route::put('/{userLink}', [UserLinkController::class, 'update']);
                Route::delete('/{userLink}', [UserLinkController::class, 'destroy']);

            });

            Route::prefix('notifications')->group(function () {

                Route::get('/', [NotificationController::class, 'getNotifications']);

                Route::post('/{notificationId}/read', [NotificationController::class, 'markAsRead']);

                Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);

                Route::get('/unread', [NotificationController::class, 'getUnreadNotifications']);

            });


            Route::prefix('posts')->controller(PostController::class)->group(function () {
                Route::get('/', 'index');
                Route::get('/reels', 'getReels');
                Route::get('/stories', 'getStories');
                Route::get('/following-posts', 'getFollowingPosts');
                Route::post('/', 'store');
                Route::get('/{post}', 'show');
                Route::put('/{post}', 'update');
                Route::delete('/{post}', 'destroy');
                Route::get('/{user}/posts', 'userPosts');
                Route::get('/{user}/reels', 'userReels');
                Route::get('/{user}/stories', 'userStories');

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

            Route::prefix('categories')->controller(CategoryController::class)->group(function () {
                Route::get('/', 'index');
                Route::post('/', 'store');
                Route::get('/{category}', 'show');
                Route::put('/{category}', 'update');
                Route::delete('/{category}', 'destroy');

            });

            Route::prefix('products-orders')->controller(ProductOrderController::class)->group(function () {
                Route::get('/', 'index');
                Route::post('/', 'store');
                Route::get('/{product}', 'show');

            });


            Route::middleware(['creator'])->group(function () {

                Route::prefix('courses')->controller(CourseController::class)->group(function () {
                    Route::get('/', 'index');
                    Route::post('/', 'store');
                    Route::get('/{course}', 'show');
                    Route::put('/{course}', 'update');
                    Route::delete('/{course}', 'destroy');

                });

                Route::prefix('modules')->controller(ModuleController::class)->group(function () {
                    Route::get('/', 'index');
                    Route::post('/', 'store');
                    Route::get('/{module}', 'show');
                    Route::put('/{module}', 'update');
                    Route::delete('/{module}', 'destroy');

                });

                Route::prefix('lessons')->controller(LessonController::class)->group(function () {
                    Route::get('/', 'index');
                    Route::post('/', 'store');
                    Route::get('/{lesson}', 'show');
                    Route::put('/{lesson}', 'update');
                    Route::delete('/{lesson}', 'destroy');

                });


                Route::prefix('products')->controller(ProductController::class)->group(function () {
                    Route::get('/', 'index');
                    Route::post('/', 'store');
                    Route::get('/{product}', 'show');
                    Route::put('/{product}', 'update');
                    Route::delete('/{product}', 'destroy');

                });

                Route::prefix('insights')->controller(InsightController::class)->group(function () {
                    Route::get('/', 'index');

                });

                Route::prefix('wallet')->controller(WalletController::class)->group(function () {
                    Route::post('/transfer/visa', 'transferToVisa');
                    Route::get('/', 'index');

                });

                // Route::post('/wallet/transfer/visa', [WalletController::class, 'transferToVisa']);
                // Route::post('/wallet/transfer/paypal', [WalletController::class, 'transferToPayPal']);

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

