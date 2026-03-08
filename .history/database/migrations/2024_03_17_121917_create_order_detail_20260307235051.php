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
        Schema::create('order_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('item_id');
            $table->string('comment');
            $table->decimal('qty',18,2);
            $table->decimal('unit_price',18,2);
            $table->string('status')->default('pending');//pending|maked|ready
            $table->timestamp('pending_time')->nullable();
            $table->timestamp('maked_time')->nullable();
            $table->timestamp('ready_time')->nullable();
            $table->boolean('is_void')->default(false);
            $table->string('void_reason')->default('');
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('order_header')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('item')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_detail');
    }
};
