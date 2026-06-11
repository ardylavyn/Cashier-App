<?php

namespace Database\Factories;

use App\Models\Products;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Products>
 */
class ProductsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'image' => fake()->imageUrl(),
            'name' => fake()->randomElement([
                'Indomie Goreng',
                'Teh Botol',
                'Aqua 600ml',
                'Coca Cola',
                'Good Day Cappuccino',
            ]),
            'price' => fake()->numberBetween(1000, 100000),
            'stock' => fake()->numberBetween(1, 500),
        ];
    }
}
