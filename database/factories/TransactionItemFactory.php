<?php

namespace Database\Factories;

use App\Models\Products;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionItemFactory extends Factory
{
    public function definition(): array
    {
        $product = Products::inRandomOrder()->first();
        $quantity = fake()->numberBetween(1, 5);

        return [
            'transaction_id' => null, // nanti diisi seeder
            'product_id' => $product->id,
            'price' => $product->price,
            'quantity' => $quantity,
            'subtotal' => $product->price * $quantity,
        ];
    }
}
