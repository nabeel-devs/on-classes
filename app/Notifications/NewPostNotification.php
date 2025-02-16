<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\Follow;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NewPostNotification extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(Public Post $post)
    {
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'post',
            'post_id' => $this->post->id,
            'user_dp' => $this->post->user->getDpUrl(),
            'user' => $this->post->user,
            'message' => "A new post has been created.",
            'is_following' => $this->isFollowing($notifiable) // Check follow status
        ];
    }

    /**
     * Store notification in the database.
     */
    public function toDatabase($notifiable)
    {
        return [
            'type' => 'post',
            'post_id' => $this->post->id,
            'user_dp' => $this->post->user->getDpUrl(),
            'user' => $this->post->user,
            'message' => "A new post has been created.",
            'is_following' => $this->isFollowing($notifiable) // Check follow status
        ];
    }

    /**
     * Check if the notifiable user is following the post creator.
     */
    private function isFollowing($notifiable)
    {
        return Follow::where('follower_id', $notifiable->id)
            ->where('following_id', $this->post->user->id)
            ->exists();
    }
}
