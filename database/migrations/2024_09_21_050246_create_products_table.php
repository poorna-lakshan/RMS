<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('itemid')->unique();
            $table->string('description');
            $table->unsignedBigInteger('itemclass_id');
            $table->boolean('active')->default(true);
            $table->boolean('inactive')->default(false);
            $table->unsignedBigInteger('itemcategory_id');
            $table->decimal('unit_cost', 8, 2)->nullable();
            $table->decimal('selling_price', 8, 2)->nullable();
            $table->string('uom');
            $table->decimal('price2', 8, 2)->nullable();
            $table->decimal('price3', 8, 2)->nullable();
            $table->string('cost_method')->nullable();
            $table->integer('reorder_level')->nullable();
            $table->string('preferred_vendor_id')->nullable();
            $table->integer('minimum_quantity')->nullable();
            $table->boolean('kot_check')->default(false);
            $table->boolean('bot_check')->default(false);
            $table->string('barcode')->nullable();
            $table->string('warehouse');
            $table->string('custom1')->nullable();
            $table->string('custom2')->nullable();
            $table->string('custom3')->nullable();
            $table->string('custom4')->nullable();
            $table->string('custom5')->nullable();
            $table->string('image')->nullable(); // To store image paths
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
