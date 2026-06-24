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
            'payment_id' => $this->payment_id,
            'order_id' => $this->order_id,
            'payment_method' => $this->payment_method->value,
            'status' => $this->status->value,
            'amount' => $this->amount,
            'gateway_response' => $this->gateway_response,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
