<?php

namespace App\Http\Resources\user;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'email' => $this->email,
            'dob' => $this->dob,
            'gender' => $this->gender,
            'phone' => $this->phone,
            'role' => $this->role,
            'online' => $this->online,
            'bio' => $this->bio,
            'profession' => $this->profession,
            'provider' => $this->provider,
            'provider_id' => $this->provider_id,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_subscribed' => $this->subscribed('default'),
            'is_following' => auth()->check()
                ? auth()->user()->followings()->where('following_id', $this->id)->exists()
                : false,
            'dp_url' => $this->getDpUrl(),
            'cover' => $this->getCover(),
        ];
    }
}
