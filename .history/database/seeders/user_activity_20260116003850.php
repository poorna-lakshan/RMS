<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class user_activity extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_activity')->insert([
            ['name' => 'Dashboard', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'Menu Items', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Menu Categories', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kitchen Stations', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Table Master', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'Inventory Items', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Inventory Categories', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Inventory Classes', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Units of Measure', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Warehouse', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'BOM', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Stock Transfer', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Stock Adjustment', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'Vendors', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Goods Received', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Supplier Payments', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'Customers', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Stewards', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'POS', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'InvoiceList', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kitchen Display', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'Sales Summary', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sales Details', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sales by Item', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sales by Category', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sales by Channels', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Fast Moving', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'Stock Status', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Expiry Report', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Food Cost Report', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Stock Movement', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'Table Turnover', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Waiter Performance', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'Reorder Report', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Outstanding', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Purchase Summary', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'Company Settings', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'System Logs', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'User Management', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Role & Permissions', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

}
