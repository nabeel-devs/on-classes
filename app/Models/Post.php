<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $guarded = [];

    protected $casts = [
        'is_story' => 'bool'
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('posts')
             ->acceptsMimeTypes(['image/jpeg', 'image/png', 'video/mp4'])
             ->singleFile();

        $this->addMediaCollection('music')
             ->singleFile();
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(PostComment::class)->whereNull('comment_id')->with('replies');
    }


    public function likes()
    {
        return $this->hasMany(PostLike::class, 'post_id')->where('is_liked', true);
    }

    public function bookmarks()
    {
        return $this->hasMany(PostBookmark::class);
    }
}
