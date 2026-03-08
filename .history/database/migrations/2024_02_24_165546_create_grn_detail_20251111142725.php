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
        Schema::create('grn_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gen_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('sku_id');
            $table->decimal('qoh', 18, 2);
            $table->decimal('qty', 18, 2);
            $table->decimal(column: 'discount_pra', total: 18, places: 2);
            $table->decimal(column: 'discount', total: 18, places: 2);
            $table->decimal('unit_cost', 18, 2);
            $table->decimal('price_level1', 18, 2);
            $table->decimal('price_level2', 18, 2)->default(0);
            $table->decimal('price_level3', 18, 2)->default(0);
            $table->decimal('free_qty', 18, 2)->default(0);
            $table->decimal('total_cost', 18, 2);
            $table->date('expire_date')->nullable();
            $table->timestamps();

            $table->foreign('gen_id')->references('id')->on('grn_header')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('item')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grn_detail');
    }
};
