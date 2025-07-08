<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'price' => $this->price,
            'quantity' => $this->quantity,
            'store' => $this->store->name ?? null,
            'seller' => $this->seller->name ?? null,
            'product_image_url' => $this->product_image_url,
            'product_image_id' => $this->product_image_id,
            'created_at' => $this->created_at,
        ];
    }
}
