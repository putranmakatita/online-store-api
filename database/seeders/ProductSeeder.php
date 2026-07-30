<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Khusus seed flash sale
        Product::factory()
            ->flashSale()
            ->create();

        // produk lain
        Product::factory()
            ->count(20)
            ->create();
    }
}
