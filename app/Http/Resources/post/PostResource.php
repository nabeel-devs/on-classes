<?php

namespace App\Http\Resources\post;

use Illuminate\Http\Request;
use App\Http\Resources\user\UserResource;
use App\Http\Resources\user\CommentResource;
use Egulias\EmailValidator\Parser\Comment;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
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
            'content' => $this->content,
            'type' => $this->type,
            'who_can_reply' => $this->who_can_reply,
            'scheduled_at' => $this->scheduled_at,
            'media_url' => $this->getFirstMediaUrl('posts'),
            'user_id' => $this->user_id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'liked_by_auth_user' => $this->liked_by_auth_user ?? false, // Whether the authenticated user liked the post
            'bookmarked_by_auth_user' => $this->bookmarked_by_auth_user ?? false, // Whether the authenticated user bookmarked the post
            'user' => new UserResource($this->whenLoaded('user')),
            'likes' => $this->likes->count(),
            'comments' => new CommentResource($this->whenLoaded('comments')),

        ];
    }
}
