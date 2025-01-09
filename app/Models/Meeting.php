<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $guarded = [];

    public function participants()
    {
        return $this->hasMany(MeetingParticipant::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
