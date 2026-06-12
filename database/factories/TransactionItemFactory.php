<?php

namespace Database\Factories;

use App\Models\Products;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionItemFactory extends Factory
{
    public function definition(): array
    {
        $price = fake()->numberBetween(1000, 100000);
        $quantity = fake()->numberBetween(1, 10);

        return [
            'transaction_id' => Transaction::inRandomOrder()->first()->id,
            'product_id' => Products::inRandomOrder()->first()->id,
            'price' => $price,
            'quantity' => $quantity,
            'subtotal' => $price * $quantity,
        ];
    }
}
