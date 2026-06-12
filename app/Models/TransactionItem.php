<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'price',
        'quantity',
        'subtotal',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'price' => 'decimal:2',
        'price' => 'integer',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product()
    {
        return $this->belongsTo(Products::class);
    }
}
