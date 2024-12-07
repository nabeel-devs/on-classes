<?php

namespace App\Http\Resources\creator;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
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
            'video' => $this->getFirstMediaUrl('video'),
            'thumbnail' => $this->getFirstMediaUrl('thumbnail'),
            'module' => new ModuleResource($this->whenLoaded('module')),
            'course' => new CourseResource($this->whenLoaded('course')),
        ];
    }
}
