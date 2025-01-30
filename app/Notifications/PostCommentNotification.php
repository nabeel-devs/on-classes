<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
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
            'message' => "{$this->user->fullName()} has commented on your post."
        ];
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable)
    {
        return [
            'type' => 'comment', // Corrected type to 'like'
            'post_id' => $this->post->id,
            'user_id' => $this->user->id, // Added user_id for reference
            'message' => "{$this->user->fullName()} has commented on your post."
        ];
    }
}
