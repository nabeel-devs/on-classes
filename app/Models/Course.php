<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;

class Course extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = [];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('thumbnail')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg'])
            ->singleFile();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function modules()
    {
        return $this->hasMany(Module::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_courses')
                    ->withPivot('quantity', 'purchase_price', 'course_order_id')
                    ->withTimestamps();
    }

    public function bookmarks()
    {
        return $this->hasMany(CourseBookmark::class);
    }

    public function orders()
    {
        return $this->hasManyThrough(
            CourseOrder::class,
            CourseOrderItem::class,
            'course_id',        // Foreign key on CourseOrderItem
            'id',               // Local key on CourseOrder
            'id',               // Local key on Course
            'course_order_id'   // Foreign key on CourseOrderItem
        );
    }

    public function orderItems()
    {
        return $this->hasMany(CourseOrderItem::class);
    }



}
