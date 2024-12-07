<?php

namespace App\Http\Resources\creator;

use Illuminate\Http\Request;
use App\Http\Resources\user\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'is_free' => $this->is_free,
            'allow_download' => $this->allow_download,
            'strict_flow' => $this->strict_flow,
            'modules' => ModuleResource::collection($this->whenLoaded('modules')),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
