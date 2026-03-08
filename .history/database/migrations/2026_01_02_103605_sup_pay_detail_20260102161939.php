<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SupPayDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sup_pay_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pay_id');
            $table->unsignedBigInteger('ware_house_id');
            $table->unsignedBigInteger(column: 'grn_id');
            $table->unsignedBigInteger(column: 'total');
            $table->unsignedBigInteger(column: 'due');
            $table->unsignedBigInteger(column: 'paid');
            $table->timestamps();

            $table->foreign('pay_id')->references('id')->on('sup_pay_header')->onDelete('cascade');
            $table->foreign('ware_house_id')->references('id')->on('ware_house')->onDelete('cascade');
            $table->foreign('grn_id')->references('id')->on('grn_header')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sup_pay_detail');
    }
}
