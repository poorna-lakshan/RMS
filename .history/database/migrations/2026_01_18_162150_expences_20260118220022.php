<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Expences extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('expences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ware_house_id');
            $table->unsignedBigInteger('category_id');
            $table->string('description');
            $table->decimal('amount', 18, 2);
            $table->string('user');
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('expence_category')->onDelete('cascade');
            $table->foreign('ware_house_id')->references('id')->on('ware_house')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::dropIfExists('expences');
    }
}
