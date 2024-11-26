<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiplomaResource extends JsonResource
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
            'document_type' => $this->document_type,
            'size' => $this->size,
            'diploma_style' => $this->diploma_style,
            'degree_type' => $this->degree_type,
            'major' => $this->major,
            'concentration' => $this->concentration,
            'university_name' => $this->university_name,
            'university_city' => $this->university_city,
            'university_state' => $this->university_state,
            'student_name' => $this->student_name,
            'student_city' => $this->student_city,
            'graduation_date' => Carbon::parse($this->graduation_date)->toDateString(),

            'user_id' => $this->user_id, // Include user_id
            'media' => [
                'signature' => $this->getMediaUrl('signature'),
                'logo' => $this->getMediaUrl('logo'),
                'seal' => $this->getMediaUrl('seal'),
            ],
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
