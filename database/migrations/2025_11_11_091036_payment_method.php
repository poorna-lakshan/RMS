<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PaymentMethod extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('payment_method', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->decimal(column: 'charge', total: 18, places: 2)->default(0);
             $table->boolean('is_sync')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::dropIfExists('payment_method');
    }
}
