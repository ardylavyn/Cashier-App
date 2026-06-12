<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionItemRecource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'product' => new ProductsResource($this->whenLoaded('product')),

            'price' => $this->price,
            'quantity' => $this->quantity,
            'subtotal' => $this->subtotal,
        ];
    }
}
