<?php

namespace App\Http\Resources\user;

use Illuminate\Http\Request;
use App\Http\Resources\user\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $authUserId = auth()?->id();
        return [
            'id' => $this->id,
            'comment' => $this->comment,
            'user_id' => $this->user_id,
            'comment_id' => $this->comment_id,
            'likes_count' => $this->likes->count(),
            'liked_by_auth_user' => $authUserId ? $this->likes->contains('user_id', $authUserId) : false,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'replies' => CommentResource::collection($this->whenLoaded('replies')),
        ];
    }
}
