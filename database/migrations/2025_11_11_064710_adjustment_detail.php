<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AdjustmentDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
          Schema::create('adjustment_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('adjustment_id');
            $table->integer('line_no');
            $table->unsignedBigInteger('item_id');
            $table->string('reason')->default(null);
            $table->decimal(column: 'qoh', total: 18, places: 2);
            $table->decimal(column: 'qty', total: 18, places: 2);
            $table->decimal(column: 'unit_cost', total: 18, places: 2);
            $table->decimal('price_level1', 18, 2);
            $table->decimal('price_level2', 18, 2)->default(0);
            $table->decimal('price_level3', 18, 2)->default(0);
            $table->decimal(column: 'line_cost', total: 18, places: 2);
            $table->decimal(column: 'line_price_level1', total: 18, places: 2);
            $table->boolean('is_void')->default(false);
            $table->timestamps();

            $table->foreign('adjustment_id')->references('id')->on('adjustment_header')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('item')->onDelete('cascade');

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
