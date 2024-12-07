<?php

namespace App\Http\Resources\creator;

use Illuminate\Http\Request;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\user\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,

            'name' => $this->name,
            'price' => $this->price,
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'cover_image' => $this->whenLoaded('media', function () {
                return $this->getFirstMediaUrl('cover_image');
            }),
            'detail_images' => $this->whenLoaded('media', function () {
                return $this->getMedia('detail_images')->map(function ($media) {
                    return $media->getUrl();
                });
            }),
            'source_file' => $this->whenLoaded('media', function () {
                return $this->getFirstMediaUrl('source_file');
            }),
            'user' => new UserResource($this->whenLoaded('user')),
            'category' => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}
