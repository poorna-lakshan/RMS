<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class user_role extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_role')->insert([
            ['name' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'cashier', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'chef', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'manager', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
