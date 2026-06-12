<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'customer_id',
        'subtotal',
        'tax',
        'total',
        'status',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function scopeSearch($query, $search)
    {
        return $query->when($search, function ($query, $search) {
            $query->where('code', 'like', "%{$search}%");
        });
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function refund()
    {
        return $this->hasOne(TransactionRefund::class);
    }
}
