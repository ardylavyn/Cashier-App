<?php

namespace Database\Seeders;

use App\Models\Products;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Database\Seeder;

class TransactionsSeeder extends Seeder
{
    public function run(): void
    {
        Transaction::factory(10)->create()->each(function ($transaction) {

            $subtotal = 0;

            // tiap transaksi punya 2–4 item
            $products = Products::inRandomOrder()->limit(rand(2, 4))->get();

            foreach ($products as $product) {

                $qty = rand(1, 5);
                $itemSubtotal = $product->price * $qty;

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'price' => $product->price,
                    'quantity' => $qty,
                    'subtotal' => $itemSubtotal,
                ]);

                $subtotal += $itemSubtotal;

                // optional: stock logic biar realistis
                $product->decrement('stock', $qty);
            }

            $tax = $subtotal * 0.11;
            $total = $subtotal + $tax;

            $transaction->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
            ]);
        });
    }
}
