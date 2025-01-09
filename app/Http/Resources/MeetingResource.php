<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\user\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class MeetingResource extends JsonResource
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
            'topic' => $this->topic,
            'date' => $this->date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'description' => $this->description,
            'livestream' => $this->livestream,
            'meet_link' => $this->meet_link,
            'creator' => new UserResource($this->whenLoaded('creator')),

            'participants' => ParticipantResource::collection($this->participants),
        ];
    }
}
