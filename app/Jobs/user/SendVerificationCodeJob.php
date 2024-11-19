<?php

namespace App\Jobs\user;

use App\Models\User;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\user\VerificationCodeNotification;

class SendVerificationCodeJob implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $code;


    public function __construct(User $user, $code)
    {
        $this->user = $user;
        $this->code = $code;
    }


    public function handle()
    {
        $this->user->notify(new VerificationCodeNotification($this->code));
    }
}
