<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'TRX-'.strtoupper(fake()->bothify('#####')),
            'customer_id' => Customer::inRandomOrder()->first()->id,
            'subtotal' => 0,
            'tax' => 0,
            'total' => 0,
            'status' => 'completed',
        ];
    }
}
