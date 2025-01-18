<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseOrder extends Model
{
    protected $guarded = [];
    public function items()
    {
        return $this->hasMany(CourseOrderItem::class);
    }
}
