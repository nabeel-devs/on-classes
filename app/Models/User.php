<?php

namespace App\Models;

use Illuminate\Support\Str;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Notifications\Notifiable;
use App\Jobs\user\SendVerificationCodeJob;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail, HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, Billable, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'dob',
        'gender',
        'phone',
        'role',
        'verification_code',
        'verification_code_expires_at',
        'provider',
        'provider_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10);

        $this->addMediaConversion('preview')
            ->width(300)
            ->height(300);
    }


    public function sendEmailVerificationNotification()
    {
        $verificationCode = random_int(100000, 999999);

        $this->verification_code = $verificationCode;
        $this->verification_code_expires_at = now()->addMinutes(10);
        $this->save();

        // Send the code to the user's email
        SendVerificationCodeJob::dispatch($this, $verificationCode);

        return $verificationCode;

    }

    public function getDpUrl($conversion = 'preview'): string
    {
        return $this->getFirstMediaUrl('dp', $conversion) ?: asset('assets/img/default-dp.png');
    }


    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    public function followers()
    {
        return $this->hasMany(Follow::class, 'following_id');
    }

    public function followings()
    {
        return $this->hasMany(Follow::class, 'follower_id');
    }

    public function links()
    {
        return $this->hasMany(UserLink::class);
    }


}
