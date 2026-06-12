<?php

namespace Database\Seeders;

use App\Models\TransactionItem;
use Illuminate\Database\Seeder;

class TransactionItemsSeeder extends Seeder
{
    public function run(): void
    {
        TransactionItem::factory()->count(10)->create();
    }
}
