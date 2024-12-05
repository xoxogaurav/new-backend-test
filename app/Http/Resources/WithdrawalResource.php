<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawalResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'payment_details' => $this->payment_details,
            'created_at' => $this->created_at->toISOString(),
            'processed_at' => $this->processed_at ? $this->processed_at->toISOString() : null
        ];
    }
}