<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use App\Models\Products;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = ProductCategory::all();

        foreach ($categories as $category) {
            Products::factory()->count(10)->create([
                // 'product_category_id' dari table migration
                'product_category_id' => $category->id,
            ]);
        }
    }
}
