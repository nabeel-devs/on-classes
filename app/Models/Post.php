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
        'is_story' => 'bool',
        'is_poll' => 'bool',
        'poll_options' => 'array',
        'poll_end_at' => 'datetime'
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

    public function pollVotes()
    {
        return $this->hasMany(PollVote::class);
    }

    public function hasUserVoted(User $user)
    {
        return $this->pollVotes()->where('user_id', $user->id)->exists();
    }

    public function getUserVote(User $user)
    {
        return $this->pollVotes()->where('user_id', $user->id)->first();
    }

    public function getPollResults()
    {
        if (!$this->is_poll) {
            return null;
        }

        $results = [];
        $totalVotes = $this->pollVotes()->count();

        foreach ($this->poll_options as $option) {
            $votes = $this->pollVotes()->where('option', $option)->count();
            $percentage = $totalVotes > 0 ? round(($votes / $totalVotes) * 100) : 0;

            $results[$option] = [
                'votes' => $votes,
                'percentage' => $percentage
            ];
        }

        return $results;
    }
}
