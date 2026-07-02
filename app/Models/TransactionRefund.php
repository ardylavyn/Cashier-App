<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionRefund extends Model
{
    protected $fillable = [
        'transaction_id',
        'reason',
        'return_to_stock',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
