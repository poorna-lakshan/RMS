<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemKitchenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_kitchen', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key for the pivot table
            $table->foreign('item_id')->references('id')->on('item')->onDelete('cascade');
            $table->foreign('kitchen_id')->references('id')->on('kitchen')->onDelete('cascade');
            $table->timestamps(); // Timestamps for created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_kitchen');
    }
}
