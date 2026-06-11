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

    public function scopeByCategory($query, $categoryName)
    {
        return $query->when($categoryName, function ($query, $categoryName) {
            // Untuk setiap category yang ditemukan melalui relasi productCategory, lakukan pengecekan. Dan bawa variabel $categoryName (yang berasal dari input user) ke dalam function ini supaya bisa digunakan untuk filtering.
            $query->whereHas('productCategory', function ($category) use ($categoryName) {
                $category->where('name', 'like', "%{$categoryName}%");
            });
        });
    }

    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class);
    }
}
