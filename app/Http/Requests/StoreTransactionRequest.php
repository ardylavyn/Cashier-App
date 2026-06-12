<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    // ===== REQUEST ITU DARI INPUTAN USER (KASIR) =====
    public function rules(): array
    {
        return [
            // ===== TRANSACTIONS =====
            'customer_id' => 'required|exists:customers,id',

            // ===== BUKAN KOLOM DATABASE =====
            // items -> daftar barang yang dibeli dalam satu transaksi makanya pakai array.
            'items' => 'required|array|min:1',

            // ===== TRANSACTION_ITEMS =====
            // Untuk SEMUA item yang ada di dalam array items, cek field berikut..
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }
}
