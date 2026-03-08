<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SupPayHeader extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('sup_pay_header', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->date('date');
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('ware_house_id');
            $table->unsignedBigInteger('payment_method_id');
            $table->string(column: 'gl_acc')->nullable();
            $table->boolean('is_confirm')->default(false);
            $table->boolean('is_void')->default(false);
            $table->timestamps();

            $table->foreign('vendor_id')->references('id')->on('vendor')->onDelete('cascade');
            $table->foreign('ware_house_id')->references('id')->on('ware_house')->onDelete('cascade');
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
         Schema::dropIfExists('sup_pay_header');
    }
}
