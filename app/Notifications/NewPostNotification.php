<?php

namespace App\Notifications;

use App\Models\Post;
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
            'message' => "A new post has been created."
        ];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'post',
            'post_id' => $this->post->id,
            'user_dp' => $this->post->user->getDpUrl(),
            'user' => $this->post->user,
            'message' => "A new post has been created."
        ];
    }
}
