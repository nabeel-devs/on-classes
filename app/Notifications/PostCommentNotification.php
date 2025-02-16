<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use App\Models\Follow;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PostCommentNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Post $post, public User $user)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'comment',
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'user_dp' => $this->user->getDpUrl(),
            'user' => $this->user,
            'message' => "{$this->user->fullName()} has commented on your post.",
            'is_following' => $this->isFollowing($notifiable) // Check follow status
        ];
    }

    /**
     * Store notification in the database.
     */
    public function toDatabase($notifiable)
    {
        return [
            'type' => 'comment',
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'user_dp' => $this->user->getDpUrl(),
            'user' => $this->user,
            'message' => "{$this->user->fullName()} has commented on your post.",
            'is_following' => $this->isFollowing($notifiable) // Check follow status
        ];
    }

    /**
     * Check if the notifiable user is following the commenter.
     */
    private function isFollowing($notifiable)
    {
        return Follow::where('follower_id', $notifiable->id)
            ->where('following_id', $this->user->id)
            ->exists();
    }
}
