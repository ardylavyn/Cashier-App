<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductCategoryFactory extends Factory
{
    protected $model = ProductCategory::class;

    public function definition(): array
    {
        $categories = [
            'Makanan',
            'Minuman',
            'Snack',
            'Elektronik',
            'Perabotan',
        ];

        $name = fake()->randomElement($categories);

        return [
            'image' => fake()->imageUrl(640, 480, 'products'),
            'name' => $name,
            'description' => "Kategori {$name}",
        ];
    }
}
