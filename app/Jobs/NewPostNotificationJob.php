<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Notifications\NewPostNotification;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class NewPostNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Post $post)
    {
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $authUserId = $this->post->user_id;
        $followingIds = DB::table('follows')
            ->where('follower_id', $authUserId)
            ->pluck('following_id');

        $users = User::whereIn('id', $followingIds)->get();

        foreach ($users as $user) {
            Notification::send($user, new NewPostNotification($this->post));
        }
    }



}
