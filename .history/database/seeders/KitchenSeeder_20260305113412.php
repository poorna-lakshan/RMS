<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class KitchenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('kitchen')->updateOrInsert(
            ['code' => 'KOT'], // unique key
            [
                'name' => 'KOT',
                'printer_name' => 'kot',
                'created_at' => now(),
                'updated_at' => now(),

            ]
        );
        DB::table('kitchen')->updateOrInsert(
            ['code' => 'BOT'], // unique key
            [
                'name' => 'BOT',
                'printer_name' => 'pos',
                'created_at' => now(),
                'updated_at' => now(),

            ]
        );
    }
}
