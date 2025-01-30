<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;

class Message extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = [];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('media')
             ->acceptsMimeTypes(['image/jpeg', 'image/png', 'video/mp4'])
             ->singleFile();
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
