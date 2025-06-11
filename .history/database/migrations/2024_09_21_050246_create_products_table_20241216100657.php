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
        Schema::create('item', function (Blueprint $table) {
            $table->string('code')->unique();
            $table->string('description');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('category_id');
            $table->string('uom');
            $table->string('costing_method');
            $table->integer('vendor_id');
            $table->integer('sales_acc');
            $table->integer('cost_of_sales_acc');
            $table->integer('inventory_acc');
            $table->decimal('unit_cost',18,2);
            $table->decimal('price_level1',18,2);
            $table->decimal('price_level2',18,2);
            $table->decimal('price_level3',18,2);
            $table->decimal('discount_amt',18,2);
            $table->decimal('discount_presentage',18,2);
            $table->decimal('reorder_qty',18,2);
            $table->decimal('minimum_qty',18,2);
            $table->string('barcode');
            $table->boolean('kot');
            $table->boolean('bot');
            $table->string('custom1');
            $table->string('custom2');
            $table->string('custom3');
            $table->string('custom4');
            $table->string('custom5');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('class_id')->references('id')->on('item_class')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('item_category')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item');
    }
};
