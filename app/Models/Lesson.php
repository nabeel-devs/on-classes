<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;

class Lesson extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = [];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('video')
            ->acceptsMimeTypes(['video/mp4', 'video/mkv', 'video/webm'])
            ->singleFile();

        $this->addMediaCollection('thumbnail')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg'])
            ->singleFile();
    }
}