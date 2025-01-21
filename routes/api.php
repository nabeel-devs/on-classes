<?php

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Stripe\FinancialConnections\Transaction;
use App\Http\Controllers\api\CategoryController;
use App\Http\Controllers\api\user\ChatController;
use App\Http\Controllers\api\user\PostController;
use App\Http\Controllers\api\user\UserController;
use App\Http\Controllers\api\user\EventController;
use App\Http\Controllers\api\user\FollowController;
use App\Http\Controllers\api\user\ContactController;
use App\Http\Controllers\api\user\DiplomaController;
use App\Http\Controllers\api\user\MessageController;
use App\Http\Controllers\api\user\ProfileController;
use App\Http\Controllers\api\user\PostLikeController;
use App\Http\Controllers\api\user\UserLinkController;
use App\Http\Controllers\api\creator\CourseController;
use App\Http\Controllers\api\creator\LessonController;
use App\Http\Controllers\api\creator\ModuleController;
use App\Http\Controllers\api\creator\WalletController;
use App\Http\Controllers\api\creator\InsightController;
use App\Http\Controllers\api\creator\MeetingController;
use App\Http\Controllers\api\creator\ProductController;
use App\Http\Controllers\api\user\PostCommentController;
use App\Http\Controllers\api\user\ProductFeedController;
use App\Http\Controllers\api\user\NotificationController;
use App\Http\Controllers\api\user\PostBookmarkController;
use App\Http\Controllers\api\user\ProductOrderController;
use App\Http\Controllers\api\user\ProductReviewController;
use App\Http\Controllers\api\creator\ParticipantController;
use App\Http\Controllers\api\creator\TransactionController;
use App\Http\Controllers\api\creator\WithdrawRequestController;

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

    Route::prefix('profile')->group(function () {

        Route::get('/{user}/profile-info', [ProfileController::class, 'showUserProfile']);

    });

    Route::get('/all-posts', [PostController::class, 'allPosts']);
    Route::get('/all-posts/{user}', [PostController::class, 'userNonAuthPosts']);


    Route::middleware(['auth'])->group(function () {

        Route::post('/email/verify', [App\Http\Controllers\api\user\EmailVerificationController::class, 'verify']);
        Route::post('/email/resend', [App\Http\Controllers\api\user\EmailVerificationController::class, 'resend']);


        Route::middleware(['verified'])->group(function () {
            Route::post('/create-password', [App\Http\Controllers\api\user\AuthController::class, 'createPassword']);


            Route::post('/subscribe', [App\Http\Controllers\api\user\SubscriptionController::class, 'subscribe']);
            Route::post('/subscription/payment-method', [App\Http\Controllers\api\user\SubscriptionController::class, 'updatePaymentMethod']);
            Route::get('/subscription/status', [App\Http\Controllers\api\user\SubscriptionController::class, 'status']);
            Route::get('/subscription/plans', [App\Http\Controllers\api\user\SubscriptionController::class, 'getPlans']);


            Route::get('/all-creators', [App\Http\Controllers\api\user\UserController::class, 'allCreators']);
            Route::get('/top-creators', [App\Http\Controllers\api\user\UserController::class, 'topCreators']);


            Route::prefix('profile')->group(function () {

                Route::get('/', [ProfileController::class, 'show']);

                Route::post('/dp', [ProfileController::class, 'uploadDp']);
                Route::post('/online-status', [ProfileController::class, 'updateOnlineStatus']);

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
                Route::get('/stories/updated', 'getStoriesUpdated');
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

            Route::prefix('events')->group(function () {

                Route::post('/', [EventController::class, 'createEvent']); // Create an event
                Route::get('/users/{user}', [EventController::class, 'userProfile']); // Create an event
                Route::post('/{event}/members', [EventController::class, 'addMember']); // Add a member
                Route::post('/{event}/token', [EventController::class, 'generateToken']); // Generate Agora token
                Route::post('/call-log', [EventController::class, 'logCall']); // Log a call

            });

            Route::prefix('chats')->group(function () {

                Route::get('/', [ChatController::class, 'index']);
                Route::get('/quick-chat-users', [ChatController::class, 'quickChat']);
                Route::get('/users-list', [ChatController::class, 'userList']);
                Route::get('/requests', [ChatController::class, 'requestChats']);
                Route::get('/archived', [ChatController::class, 'archivedChats']);
                Route::post('/', [ChatController::class, 'store']);
                Route::get('/{chat}', [ChatController::class, 'show']);
                Route::post('/{chat}/accept-request', [ChatController::class, 'acceptRequest']);
                Route::post('/{chat}/archived', [ChatController::class, 'setArchived']);
                Route::delete('/{chat}/delete', [ChatController::class, 'destroy']);

                // Message-related routes
                Route::post('/{chat}/messages', [MessageController::class, 'store']);
                Route::get('/{chat}/messages', [MessageController::class, 'index']);
                Route::patch('/messages/{message}/read', [MessageController::class, 'markAsRead']);
                Route::delete('/{chat}/{message}/delete', [MessageController::class, 'destroyMessage']);


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

            Route::prefix('products-feed')->controller(ProductFeedController::class)->group(function () {
                Route::get('/all', 'index');
                Route::get('/popular', 'popular');
                Route::get('/{categoryId}/products', 'categoryProducts');

            });

            Route::prefix('products-orders')->controller(ProductOrderController::class)->group(function () {
                Route::get('/', 'index');
                Route::post('/', 'store');
                Route::get('/user/bought-products', 'userProducts');

                Route::post('/verify-discount-code', 'verifyDiscountCode');
                Route::get('/{product}', 'show');

            });

            Route::prefix('products-reviews')->controller(ProductReviewController::class)->group(function () {
                Route::post('/', 'store');
                Route::get('/{product}', 'index');
                Route::put('/{review}', 'update');
                Route::delete('/{review}', 'destroy');


            });


            Route::middleware(['creator'])->group(function () {

                Route::get('/creator/info/{user}', [UserController::class, 'creatorInfo']);

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

                // Route::prefix('wallet')->controller(WalletController::class)->group(function () {
                //     Route::post('/transfer/visa', 'transferToVisa');
                //     Route::get('/', 'index');

                // });

                Route::prefix('withdraw-requests')->group(function () {
                    Route::post('/', [WithdrawRequestController::class, 'store']);
                    Route::get('/', [WithdrawRequestController::class, 'index']);
                    Route::get('/user', [WithdrawRequestController::class, 'userRequests']);
                    Route::get('/{id}', [WithdrawRequestController::class, 'show']);
                });

                Route::prefix('invoices')->group(function () {
                    Route::get('/', [TransactionController::class, 'index']);
                    Route::get('/wallet-info', [TransactionController::class, 'walletInfo']);
                });

                Route::prefix('meetings')->group(function () {
                    Route::post('/', [MeetingController::class, 'store']);  // Create a meeting
                    Route::get('/', [MeetingController::class, 'index']);  // Get all meetings
                    Route::get('{meeting}', [MeetingController::class, 'show']);  // Get a specific meeting
                    Route::put('{meeting}', [MeetingController::class, 'update']);  // Update a meeting
                    Route::delete('{meeting}', [MeetingController::class, 'destroy']);  // Delete a meeting
                    Route::get('{user}/user-meetings', [MeetingController::class, 'userMeetings']);

                    Route::prefix('{meeting}/participants')->group(function () {
                        Route::post('/', [ParticipantController::class, 'store']);  // Add a participant to the meeting
                        Route::delete('{participant}', [ParticipantController::class, 'destroy']);  // Remove a participant from the meeting
                        Route::get('/', [ParticipantController::class, 'index']);  // Get all participants for a meeting
                    });
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

