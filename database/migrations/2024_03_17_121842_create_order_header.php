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
            $table->string('reference_no');
            $table->string('kot');
            $table->string('type');
            $table->string('category');
            $table->unsignedBigInteger('ware_house_id');
            $table->unsignedBigInteger('customer_id');
            $table->string('table');
            $table->string('user');
            $table->string('steward');
            $table->boolean('is_void');
            $table->boolean('is_invoice');
            $table->boolean('is_paid');
            $table->boolean('is_served');
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
