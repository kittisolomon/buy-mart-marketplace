<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'order_id' => $this->order_id,
            'buyer_id' => $this->buyer_id,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'transaction_id' => $this->transaction_id,
            'total_amount' => $this->total_amount,
            'transfer_fee' => $this->transfer_fee,
            'trans_total' => $this->trans_total,
            'settlement_amount' => $this->settlement_amount,
            'currency_code' => $this->currency_code,
            'buyer' => new UserResource($this->buyer),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 
