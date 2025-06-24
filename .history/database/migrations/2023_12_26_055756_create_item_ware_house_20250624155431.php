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
        Schema::create('item_ware_house', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ware_house_id');
            $table->unsignedBigInteger('item_id');
             $table->decimal('qty',18,2);
            $table->decimal('avg_cost',18,2);
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
        Schema::dropIfExists('item_ware_house');
    }
};
