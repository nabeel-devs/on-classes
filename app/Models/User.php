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
        'first_name',
        'last_name',
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
        'provider_id',
        'bio',
        'profession',
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

    public function getMediaModel(): string
    {
        return Media::class;
    }

    public function registerMediaCollections(): void
    {

        $this->addMediaCollection('dp')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg'])
            ->singleFile();

        $this->addMediaCollection('cover')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg'])
            ->singleFile();
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

    public function fullName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getDpUrl($conversion = 'preview'): string
    {
        return $this->getFirstMediaUrl('dp') ?: asset('assets/img/default-dp.png');
    }

    public function getCover(): string
    {
        return $this->getFirstMediaUrl('cover') ?: asset('assets/img/default-dp.png');
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

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function likes()
    {
        return $this->belongsToMany(Post::class, 'post_likes')->withTimestamps();
    }

    public function bookmarks()
    {
        return $this->belongsToMany(Post::class, 'post_bookmarks')->withTimestamps();
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function bought_products()
    {
        return $this->belongsToMany(Product::class, 'user_products')
                    ->withPivot('quantity', 'purchase_price', 'order_id')
                    ->withTimestamps();
    }


    public function bought_courses()
    {
        return $this->belongsToMany(Course::class, 'user_courses')
                    ->withPivot('quantity', 'purchase_price', 'course_order_id')
                    ->withTimestamps();
    }

    public function participants()
    {
        return $this->hasMany(MeetingParticipant::class);
    }

    public function meetings()
    {
        return $this->belongsToMany(Meeting::class, 'meeting_participants');
    }



}
