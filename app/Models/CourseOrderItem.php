<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseOrderItem extends Model
{
    protected $guarded = [];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function order()
    {
        return $this->belongsTo(CourseOrder::class);
    }
}
