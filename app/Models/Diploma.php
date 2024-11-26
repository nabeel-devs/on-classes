<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;

class Diploma extends Model implements HasMedia
{

    use InteractsWithMedia;

    protected $guarded = [];


    public function getMediaUrl($collection)
    {
        $media = $this->getFirstMedia($collection);
        return $media ? $media->getUrl() : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
