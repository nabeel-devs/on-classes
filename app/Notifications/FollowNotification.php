<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Follow;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class FollowNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public User $user)
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
            'type' => 'follow',
            'user_id' => $this->user->id,
            'user_dp' => $this->user->getDpUrl(),
            'user' => $this->user,
            'message' => "{$this->user->fullName()} has started following you.",
            'is_following_back' => $this->isFollowingBack($notifiable) // Check follow back status
        ];
    }

    /**
     * Store notification in the database.
     */
    public function toDatabase($notifiable)
    {
        return [
            'type' => 'follow',
            'user_id' => $this->user->id,
            'user_dp' => $this->user->getDpUrl(),
            'user' => $this->user,
            'message' => "{$this->user->fullName()} has started following you.",
            'is_following_back' => $this->isFollowingBack($notifiable) // Check follow back status
        ];
    }

    /**
     * Check if the notifiable user is following back.
     */
    private function isFollowingBack($notifiable)
    {
        return Follow::where('follower_id', $notifiable->id)
            ->where('following_id', $this->user->id)
            ->exists();
    }
}
