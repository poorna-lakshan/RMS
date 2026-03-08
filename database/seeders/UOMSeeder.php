<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class UOMSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('uom')->insert([
            ['description' => 'unit', 'conversion_factor' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['description' => 'g', 'conversion_factor' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['description' => 'kg', 'conversion_factor' => 1000, 'created_at' => now(), 'updated_at' => now()],
            ['description' => 'ml', 'conversion_factor' => 1000, 'created_at' => now(), 'updated_at' => now()],
            ['description' => 'l', 'conversion_factor' => 1000, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
