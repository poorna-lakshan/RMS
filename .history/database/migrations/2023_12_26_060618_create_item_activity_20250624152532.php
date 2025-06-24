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
        Schema::create('item_activity', function (Blueprint $table) {
            $table->id();
            $table->integer('doc_type');
            $table->unsignedBigInteger('ware_house_id');
            $table->string('reference_no');
            $table->string('date');
            $table->string('trans_type');
            $table->string('doc_reference');
            $table->unsignedBigInteger('item_id');
            $table->decimal('qty',18,2);
            $table->decimal('unit_cost',18,2);
            $table->decimal('retail_price',18,2);
            $table->decimal('qoh',18,2);
            $table->decimal('price_level1',18,2);
            $table->decimal('price_level2',18,2);
            $table->decimal('price_level3',18,2);
            $table->string('grn_no');
            $table->boolean('is_void');
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
        Schema::dropIfExists('item_activity');
    }
};
