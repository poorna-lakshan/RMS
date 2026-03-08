<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call(ItemClassSeeder::class);
        $this->call(CASHCustomerSeeder::class);
        $this->call(KitchenSeeder::class);
        $this->call(MainWarehouseSeeder::class);
        $this->call(UOMSeeder::class);
        $this->call(CodeSeeder::class);
        $this->call(company::class);
        $this->call(user_activity::class);
        $this->call(user_role::class);
        $this->call(user_auth::class);
        $this->call(UserSeeder::class);
        $this->call(PaymentMethodSeeder::class);
    }
}
