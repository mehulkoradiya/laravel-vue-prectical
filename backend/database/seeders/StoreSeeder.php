<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Store;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stores = [
            ['name' => 'Main Warehouse', 'location' => 'Surat', 'is_active' => true],
            ['name' => 'North Branch', 'location' => 'North District', 'is_active' => true],
            ['name' => 'South Branch', 'location' => 'South District', 'is_active' => true],
            ['name' => 'East Branch', 'location' => 'East District', 'is_active' => true],
            ['name' => 'West Branch', 'location' => 'West District', 'is_active' => true],
        ];

        Store::insert($stores);

    }
}
