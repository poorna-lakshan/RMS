<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AdjustmentHeader extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
          Schema::create('adjustment_header', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->date('date');
            $table->unsignedBigInteger('ware_house_id');
            $table->string('description');
            $table->decimal(column: 'total_cost', total: 18, places: 2);
            $table->decimal(column: 'net_total', total: 18, places: 2);
            $table->string('user');
            $table->boolean('is_void')->default(false);
            $table->timestamps();

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
        //
    }
}
