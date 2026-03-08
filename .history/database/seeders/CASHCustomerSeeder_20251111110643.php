<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CASHCustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         DB::table('customer')->updateOrInsert(
            ['code' => 'CASH'], // unique key
            [
                'name' => 'CASH',
                'contact' => '',      // set your actual account codes
                'address1' => '',
                'address2' => '',
                'email' => '',
                'vat_no' => '',
                'type' => '',
                'custom1' => '',
                'custom2' => '',
                'custom3' => '',
                'custom4' => '',
                'custom5' => '',
            ]
        );
    }
}
