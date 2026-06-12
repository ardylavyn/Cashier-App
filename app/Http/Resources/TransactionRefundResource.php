<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionRefundResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'reason' => $this->reason,
            'created_at' => $this->created_at,
        ];
    }
}
