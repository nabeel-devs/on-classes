<?php

namespace App\Http\Resources\creator;

use Illuminate\Http\Request;
use App\Http\Resources\CategoryResource;
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
            'price' => $this->price,
            'is_free' => $this->is_free,
            'allow_download' => $this->allow_download,
            'strict_flow' => $this->strict_flow,
            'is_discounted' => $this->is_discounted,
            'discount_code' => $this->discount_code,
            'discount_percentage' => $this->discount_percentage,
            'who_has_access' => $this->who_has_access,
            'thumbnail' => $this->whenLoaded('media', function () {
                return $this->getFirstMediaUrl('thumbnail');
            }),
            'modules' => ModuleResource::collection($this->whenLoaded('modules')),
            'user' => new UserResource($this->whenLoaded('user')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
