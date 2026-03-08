<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InvPaymentMethod extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inv_payment_method', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trans_id');
            $table->unsignedBigInteger('payment_method_id');
            $table->decimal(column: 'amount', total: 18, places: 2)->default(0);
            $table->timestamps();

            $table->foreign('trans_id')->references('id')->on('invoice_header')->onDelete('cascade');
            $table->foreign('payment_method_id')->references('id')->on('payment_method')->onDelete('cascade');

        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inv_payment_method');
    }
}
