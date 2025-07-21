<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\CartItemResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $items = $this->relationLoaded('items') ? $this->items : collect();
        $totalQuantity = $items->sum('quantity');
        $totalPrice = $items
            ->map(function ($item) {
                $price = $item->relationLoaded('product') ? $item->product->price : 0;
                return $price * $item->quantity;
            })
            ->sum();

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'items' => CartItemResource::collection($this->whenLoaded('items')),
            'total_quantity' => $totalQuantity,
            'total_price' => $totalPrice,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
