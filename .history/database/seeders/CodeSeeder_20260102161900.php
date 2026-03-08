<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class CodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
          DB::table('default_codegen')->insert([
            ['type' => 'item', 'prefix' => 'ITM', 'pad' => 8, 'no' => 1],
            ['type' => 'bom', 'prefix' =>'BOM', 'pad' => 8, 'no' => 1],
            ['type' => 'grn', 'prefix' =>'GRN', 'pad' => 8, 'no' => 1],
            ['type' => 'inv', 'prefix' =>'INV', 'pad' => 8, 'no' => 1],
            ['type' => 'ord', 'prefix' =>'ORD', 'pad' => 8, 'no' => 1],
            ['type' => 'trn', 'prefix' =>'TRN', 'pad' => 8, 'no' => 1],
            ['type' => 'mrn', 'prefix' =>'MRN', 'pad' => 8, 'no' => 1],
            ['type' => 'crn', 'prefix' =>'CRN', 'pad' => 8, 'no' => 1],
            ['type' => 'srn', 'prefix' =>'SRN', 'pad' => 8, 'no' => 1],
            ['type' => 'ajs', 'prefix' =>'AJS', 'pad' => 8, 'no' => 1],
            ['type' => 'bat', 'prefix' =>'BAT', 'pad' => 8, 'no' => 1],
             ['type' => 'pay', 'prefix' =>'PAY', 'pad' => 8, 'no' => 1],
        ]);
    }
}
