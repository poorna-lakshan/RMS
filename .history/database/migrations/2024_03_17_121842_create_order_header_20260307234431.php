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
        Schema::create('order_header', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no');//ord000001
            $table->string('kot');//1
            $table->string('type');//DineIn|Take Away
            $table->string('category');//-,KOT,BOT
            $table->unsignedBigInteger('ware_house_id');
            $table->unsignedBigInteger('customer_id');
            $table->string('order_code')->nullable(true);
            $table->string('table');
            $table->string('user');
            $table->unsignedBigInteger('steward_id');
            $table->boolean('is_void')->default( false);
            $table->boolean('is_invoice')->default(false);
            $table->unsignedBigInteger('invoice_id')->default( false);
            $table->boolean('is_paid')->default(false);
            $table->boolean('is_print')->default(false);
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customer')->onDelete('cascade');
            $table->foreign('ware_house_id')->references('id')->on('ware_house')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_header');
    }
};
