<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('item_batch', function (Blueprint $table) {
            $table->id();
            $table->string('batch_no');
            $table->unsignedBigInteger('ware_house_id');
            $table->unsignedBigInteger('item_id');
            $table->decimal('qty',18,2);
            $table->string('purchase_no');
            $table->decimal('unit_cost',18,2);
            $table->decimal('price_level1',18,2);
            $table->decimal('price_level2',18,2);
            $table->decimal('price_level3',18,2);
            $table->decimal('expire_date',18,2);
            $table->timestamps();

            $table->foreign('ware_house_id')->references('id')->on('ware_house')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('item')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_batch');
    }
};
