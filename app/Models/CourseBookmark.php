<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseBookmark extends Model
{
    protected $guarded = [];
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
