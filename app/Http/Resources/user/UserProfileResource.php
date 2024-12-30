<?php

namespace App\Http\Resources\user;

use Illuminate\Http\Request;
use App\Http\Resources\post\PostResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
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
        'dp_url' => $this->getDpUrl(),

        // Load links and return as a collection
        'links' => UserLinkResource::collection($this->whenLoaded('links')),

        // If you have both images and videos in the posts, use a collection
        'images' => PostResource::collection($this->whenLoaded('posts')->where('type', 'image')),
        'videos' => PostResource::collection($this->whenLoaded('posts')->where('type', 'video')),
    ];
}

}
