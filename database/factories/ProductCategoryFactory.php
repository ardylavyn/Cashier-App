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
            ['Main Course', 'Menu utama dengan porsi lengkap.'],
            ['Appetizer', 'Hidangan pembuka.'],
            ['Pasta', 'Berbagai olahan pasta.'],
            ['Pizza', 'Pizza dengan berbagai topping.'],
            ['Rice Bowl', 'Rice bowl khas café.'],
            ['Snack', 'Camilan ringan.'],
            ['Dessert', 'Makanan penutup.'],
            ['Coffee', 'Minuman berbasis espresso.'],
            ['Non Coffee', 'Minuman tanpa kopi.'],
            ['Tea', 'Aneka teh pilihan.'],
            ['Fresh Juice', 'Jus buah segar.'],
            ['Smoothies', 'Minuman buah creamy.'],
            ['Mocktail', 'Minuman segar tanpa alkohol.'],
        ];

        $category = fake()->unique()->randomElement($categories);

        return [
            'image' => fake()->imageUrl(640, 480, 'food'),
            'name' => $category[0],
            'description' => $category[1],
        ];
    }
}
