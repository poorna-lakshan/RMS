<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CashierSessions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cashier_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->dateTime('in_time')->nullable();
            $table->dateTime('out_time')->nullable();
            $table->string('user');
            $table->boolean('is_in')->nullable()->default(false);
            $table->boolean('is_out')->nullable()->default(false);
            $table->decimal('in_amt',18,2)->default(0);
            $table->decimal('out_amt',18,2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
