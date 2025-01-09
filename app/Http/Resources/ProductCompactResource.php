<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductCompactResource extends JsonResource
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
            'description' => $this->description,
            'quantity' => $this->pivot->quantity, // quantity from pivot table
            'purchase_price' => $this->pivot->purchase_price, // purchase price from pivot table
            'order_id' => $this->pivot->order_id, // order ID from pivot table
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
