<?php

namespace App\Jobs\user;

use App\Models\User;
use App\Notifications\user\ResetPasswordNotification;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ResetPasswordJob implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $resetCode;


    public function __construct(User $user, $resetCode)
    {
        $this->user = $user;
        $this->resetCode = $resetCode;
    }


    public function handle()
    {
        $this->user->notify(new ResetPasswordNotification($this->resetCode));
    }
}
