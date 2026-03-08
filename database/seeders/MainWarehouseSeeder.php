<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MainWarehouseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ware_house')->updateOrInsert(
            ['code' => 'MAINWH'], // unique key
            [
                'name' => 'MAINWH',
                'ap_acc' => '1000',      // set your actual account codes
                'ar_acc' => '2000',
                'cash_acc' => '3000',
                'sales_acc' => '4000',
                'cos_acc' => '5000',
                'inv_acc' => '6000',
                'price_level' => 'price_level1',
                'address1' => 'address1',
                'address2' => 'address2',
                'city' => 'colombo',
                'state' => 'demo',
                'contry' => 'sri lanka',
                'phone' => '0771234567',
                'fax' => 'demo',
                'email' => 'demo@gmail.com',
                'service_charge' => '10',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
