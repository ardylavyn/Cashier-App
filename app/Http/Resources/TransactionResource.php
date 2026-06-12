<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'code' => $this->code,
            'status' => $this->status,

            'refund' => new TransactionRefundResource($this->whenLoaded('refund')),
            // abis whenloaded itu nama relasi yg ada di model
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'transactionItems' => TransactionItemRecource::collection($this->whenLoaded('transactionItems')),

            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'total' => $this->total,

        ];
    }
}
