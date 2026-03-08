<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bom_alternatives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bom_detail_id');
            $table->unsignedBigInteger('parent_item_id'); // main ingredient id
            $table->unsignedBigInteger('item_id'); // alternative ingredient
             $table->unsignedBigInteger('uom_id'); // alternative ingredient
            $table->decimal('qty', 18, 2)->default(0); // quantity for alternative
            $table->unsignedBigInteger('base_uom_id')->nullable();
            $table->decimal('base_qty', 18, 4)->default(0);
            $table->timestamps();

            // foreign keys
            $table->foreign('bom_detail_id')->references('id')->on('bom_detail')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('item')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bom_alternatives');
    }
};
