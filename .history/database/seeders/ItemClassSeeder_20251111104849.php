<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ItemClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('item_class')->insert([
            ['description' => 'stock','description' => 'stock', 'created_at' => now(), 'updated_at' => now()],
            ['description' => 'ingredient', 'created_at' => now(), 'updated_at' => now()],
            ['description' => 'dish', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
