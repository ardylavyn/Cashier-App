<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'image',
        'name',
        'description',
    ];

    public function scopeSearch($query, $search)
    {
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query;
    }
}
