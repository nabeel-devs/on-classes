<?php

namespace App\Notifications;

use App\Models\User;
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
            'message' => "{$this->user->fullName()} has started following you."
        ];
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable)
    {
        return [
            'type' => 'follow',
            'user_id' => $this->user->id,
            'user_dp' => $this->user->getDpUrl(),
            'user' => $this->user,
            'message' => "{$this->user->fullName()} has started following you."
        ];
    }
}
