<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class company extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         DB::table('company')->updateOrInsert(
            ['name' => 'demo'], // unique key
            [
                'name' => 'demo',
                'address1' => 'address1',
                'address2' => 'address2',
                'city' => 'colombo',
                'state' => 'demo',
                'contry' => 'sri lanka',
                'phone' => '0771234567',
                'fax' => 'demo',
                'email' => 'demo@gmail.com',
                'website' => 'www.demo.com',
                'service_charge' => '10',
                'created_at' => now(),
                'updated_at' => now(),

            ]
        );
    }
}
