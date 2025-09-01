<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Stock;
use App\Models\Store;
use Illuminate\Support\Str;

class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $storeIds = Store::pluck('id')->toArray();

        for ($i = 1; $i <= 50; $i++) {
            Stock::create([
                'stock_no' => 'STK-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'item_code' => strtoupper(Str::random(6)),
                'item_name' => 'Product ' . $i,
                'quantity' => rand(5, 100),
                'location' => 'Warehouse ' . rand(1, 5),
                'store_id' => $storeIds[array_rand($storeIds)],
                'in_stock_date' => now()->subDays(rand(0, 30)),
                'status' => ['pending', 'in_stock'][rand(0, 1)],
            ]);
        }
    }
}
