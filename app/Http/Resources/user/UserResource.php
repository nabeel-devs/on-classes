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
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'dob' => $this->dob,
            'gender' => $this->gender,
            'phone' => $this->phone,
            'role' => $this->role,
            'provider' => $this->provider,
            'provider_id' => $this->provider_id,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'dp_url' => $this->getDpUrl(), // Display Picture URL
        ];
    }
}
