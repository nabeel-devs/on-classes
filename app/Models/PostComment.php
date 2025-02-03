<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostComment extends Model
{
    protected $guarded = [];
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // A comment may have replies
    public function replies()
    {
        return $this->hasMany(PostComment::class, 'comment_id');
    }

    // A reply belongs to a parent comment
    public function parentComment()
    {
        return $this->belongsTo(PostComment::class, 'comment_id');
    }
}
