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
        $data = [
            'id' => $this->id,
            'content' => $this->content,
            'type' => $this->type,
            'who_can_reply' => $this->who_can_reply,
            'who_can_see' => $this->who_can_see,
            'scheduled_at' => $this->scheduled_at,
            'media_url' => $this->getFirstMediaUrl('posts'),
            'music_url' => $this->getFirstMediaUrl('music'),
            'user_id' => $this->user_id,
            'status' => $this->status,
            'is_story' => $this->is_story,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'liked_by_auth_user' => $this->liked_by_auth_user ?? false,
            'bookmarked_by_auth_user' => $this->bookmarked_by_auth_user ?? false,
            'user' => new UserResource($this->whenLoaded('user')),
            'likes' => [
                'count' => $this->likes->count(),
                'users' => UserResource::collection($this->whenLoaded('likes')->pluck('user'))
            ],
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
        ];

        // Add poll information if the post is a poll
        if ($this->is_poll) {
            $data['poll'] = [
                'is_poll' => true,
                'options' => $this->poll_options,
                'end_at' => $this->poll_end_at,
                'results' => $this->getPollResults(),
                'has_voted' => auth()->check() ? $this->hasUserVoted(auth()->user()) : false,
                'user_vote' => auth()->check() ? $this->getUserVote(auth()->user())?->option : null,
            ];
        }

        return $data;
    }

}
