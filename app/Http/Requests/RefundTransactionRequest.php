<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RefundTransactionRequest extends FormRequest
{
    public function rules(): array
    {
        return [

            'reason' => 'required|string|max:500',

            'items' => 'required|array|min:1',

            'items.*.transaction_item_id' => 'required|exists:transaction_items,id',

            'items.*.quantity' => 'required|integer|min:1',
        ];
    }
}
