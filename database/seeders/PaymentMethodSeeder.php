<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('payment_method')->insert([
            ['description' => 'cash', 'charge' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['description' => 'card', 'charge' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['description' => 'bank', 'charge' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['description' => 'credit', 'charge' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
