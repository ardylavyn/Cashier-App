<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Products extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_category_id',
        'image',
        'name',
        'price',
        'stock',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    public function scopeSearch($query, $search)
    {
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query;
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->when($categoryId, function ($query, $categoryId) {
            $query->where('product_category_id', $categoryId);
        });
    }

    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItems::class);
    }
}
