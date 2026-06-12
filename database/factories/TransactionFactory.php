<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(10000, 500000);
        $tax = $subtotal * 0.11;
        $total = $subtotal + $tax;

        return [
            'code' => 'TRX-'.strtoupper(fake()->bothify('#####')),
            'customer_id' => Customer::inRandomOrder()->first()->id,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ];
    }
}
