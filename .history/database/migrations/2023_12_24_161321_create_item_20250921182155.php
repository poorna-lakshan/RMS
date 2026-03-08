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
        Schema::create('item', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('description');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('uom_id');
            $table->string('type');
            $table->string('sub_category')->nullable();
            $table->string('costing_method');
            $table->integer('vendor_id')->nullable();
            $table->integer('sales_acc')->nullable();
            $table->integer('cost_of_sales_acc')->nullable();
            $table->integer('inventory_acc')->nullable();
            $table->decimal('unit_cost', 18, 2);
            $table->decimal('price_level1', 18, 2);
            $table->decimal('price_level2', 18, 2);
            $table->decimal('price_level3', 18, 2);
            $table->decimal('discount_amt', 18, 2);
            $table->decimal('discount_presentage', 18, 2);
            $table->decimal('reorder_qty', 18, 2);
            $table->decimal('minimum_qty', 18, 2);
            $table->string('barcode');
            $table->boolean('kitchen_id')->nullable();
            $table->string('custom1');
            $table->string('custom2');
            $table->string('custom3');
            $table->string('custom4');
            $table->string('custom5');
            $table->string('image')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('uom_id')->references('id')->on('uom')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('item_class')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('item_category')->onDelete('cascade');
            $table->foreign('kitchen_id')->references('id')->on('kitchen')->onDelete('cascade');
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
